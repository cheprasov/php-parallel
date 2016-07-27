<?php
/**
 * This file is part of Parallel.
 * git: https://github.com/cheprasov/php-parallel
 *
 * (C) Alexander Cheprasov <cheprasov.84@ya.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Test\Integration;
use Parallel\Storage\MemcachedStorage;

/**
 * @see \Parallel\Storage\MemcachedStorage
 */
class MemcachedStorageTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    protected function getTestServers() {
        return [explode(':', TEST_MEMCACHED_SERVER)];
    }

    /**
     * @return \Memcached
     */
    protected function getMemcached() {
        $Memcached = new \Memcached();
        $Memcached->addServers($this->getTestServers());
        $Memcached->flush();
        return $Memcached;
    }

    /**
     * @see \Parallel\Storage\MemcachedStorage::set
     */
    public function test_set() {
        $Memcached = $this->getMemcached();
        $Memcached->flush();
        $Storage = new MemcachedStorage(['servers' => $this->getTestServers()]);

        $this->assertSame(true, $Storage->set('foo', 'bar1', 'hello world'));
        $this->assertSame('"hello world"', $Memcached->get('foo:bar1'));

        $this->assertSame(true, $Storage->set('foo', 'bar2', [1, 2, 3, 4, 5]));
        $this->assertSame('[1,2,3,4,5]', $Memcached->get('foo:bar2'));

        $this->assertSame(true, $Storage->set('foo', 'bar3', ['foo', 'bar', '3', 4, 5.678]));
        $this->assertSame('["foo","bar","3",4,5.678]', $Memcached->get('foo:bar3'));

        $this->assertSame(true, $Storage->set('foo', 'bar4', null));
        $this->assertSame('null', $Memcached->get('foo:bar4'));

        $this->assertSame(true, $Storage->set('foo', 'bar5', [null, true, false]));
        $this->assertSame('[null,true,false]', $Memcached->get('foo:bar5'));

        $this->assertSame(true, $Storage->set('foo', 'bar6', ['foo' => 'bar', 'hello' => 'world']));
        $this->assertSame('{"foo":"bar","hello":"world"}', $Memcached->get('foo:bar6'));
    }

    /**
     * @see \Parallel\Storage\MemcachedStorage::get
     */
    public function test_get() {
        $Memcached = $this->getMemcached();
        $Memcached->flush();
        $Storage = new MemcachedStorage(['servers' => $this->getTestServers()]);

        $this->assertSame(true, $Memcached->set('foo1:bar0', ''));
        $this->assertSame(null, $Storage->get('foo1', 'bar0'));

        $this->assertSame(true, $Memcached->set('foo1:bar1', '[1, 2, 3, 4, 5]'));
        $this->assertSame([1, 2, 3, 4, 5], $Storage->get('foo1', 'bar1'));

        $this->assertSame(true, $Memcached->set('foo1:bar2', '{"0":true, "1":false, "2":null, "3":"4", "4":5, "5":6.789, "hello":"world"}'));
        $this->assertSame([true, false, null, '4', 5, 6.789, 'hello' => 'world'], $Storage->get('foo1', 'bar2'));

        $this->assertSame(true, $Memcached->set('foo1:bar3', '1'));
        $this->assertSame(true, $Memcached->set('foo1:bar4', '"2"'));
        $this->assertSame(true, $Memcached->set('foo1:bar5', '3.14159265'));
        $this->assertSame(true, $Memcached->set('foo1:bar6', '"hello world"'));
        $this->assertSame(true, $Memcached->set('foo1:bar7', '{"foo":"bar", "hello":"world"}'));
        $this->assertSame(true, $Memcached->set('foo1:bar8', 'null'));
        $this->assertSame(true, $Memcached->set('foo1:bar9', '-42'));

        $this->assertSame([
            'bar3' => 1,
            'bar4' => '2',
            'bar5' => 3.14159265,
        ], $Storage->get('foo1', ['bar3', 'bar4', 'bar5']));

        $this->assertSame([
            'bar6' => 'hello world',
            'bar7' => ['foo' => 'bar', 'hello' => 'world'],
        ], $Storage->get('foo1', ['bar6', 'bar7']));

        $this->assertSame([
            'bar3' => 1,
            'bar4' => '2',
            'bar5' => 3.14159265,
            'bar6' => 'hello world',
            'bar7' => ['foo' => 'bar', 'hello' => 'world'],
            'bar8' => null,
            'bar9' => -42,
        ], $Storage->get('foo1', ['bar3', 'bar4', 'bar5', 'bar6', 'bar7', 'bar8', 'bar9']));
    }


    /**
     * @see \Parallel\Storage\MemcachedStorage::del
     */
    public function test_del() {
        $Memcached = $this->getMemcached();
        $Memcached->flush();
        $Storage = new MemcachedStorage(['servers' => $this->getTestServers()]);

        $this->assertSame(true, $Memcached->set('foo2:bar0', 'foo'));
        $this->assertSame('foo', $Memcached->get('foo2:bar0'));
        $this->assertSame(1, $Storage->del('foo2', 'bar0'));
        $this->assertSame(false, $Memcached->get('foo2:bar0'));
        $this->assertSame(\Memcached::RES_NOTFOUND, $Memcached->getResultCode());

        $this->assertSame(true, $Memcached->set('foo2:bar1', 'foo'));
        $this->assertSame(true, $Memcached->set('foo2:bar2', 'bar'));
        $this->assertSame(true, $Memcached->set('foo2:bar3', '123'));

        $this->assertSame('foo', $Memcached->get('foo2:bar1'));
        $this->assertSame('bar', $Memcached->get('foo2:bar2'));
        $this->assertSame('123', $Memcached->get('foo2:bar3'));

        $this->assertSame(2, $Storage->del('foo2', ['bar1', 'bar2', 'bar2']));

        $this->assertSame(false, $Memcached->get('foo2:bar1'));
        $this->assertSame(\Memcached::RES_NOTFOUND, $Memcached->getResultCode());
        $this->assertSame(false, $Memcached->get('foo2:bar2'));
        $this->assertSame(\Memcached::RES_NOTFOUND, $Memcached->getResultCode());
        $this->assertSame('123', $Memcached->get('foo2:bar3'));

        $this->assertSame(1, $Storage->del('foo2', ['bar1', 'bar3']));
        $this->assertSame(false, $Memcached->get('foo2:bar3'));
        $this->assertSame(\Memcached::RES_NOTFOUND, $Memcached->getResultCode());
    }


}
