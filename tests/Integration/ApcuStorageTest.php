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
use Parallel\Storage\ApcuStorage;

/**
 * @see \Parallel\Storage\ApcuStorage
 */
class ApcuStorageTest extends \PHPUnit_Framework_TestCase {

    /**
     * @see \Parallel\Storage\ApcuStorage::set
     */
    public function test_set() {
        $Storage = new ApcuStorage();
        $success = null;

        $this->assertSame(true, $Storage->set('foo', 'bar1', 'hello world'));
        $this->assertSame('hello world', apcu_fetch('foo:bar1', $success));
        $this->assertSame(true, $success);

        $this->assertSame(true, $Storage->set('foo', 'bar2', [1, 2, 3, 4, 5]));
        $this->assertSame([1, 2, 3, 4, 5], apcu_fetch('foo:bar2', $success));
        $this->assertSame(true, $success);

        $this->assertSame(true, $Storage->set('foo', 'bar3', ['foo', 'bar', '3', 4, 5.678]));
        $this->assertSame(['foo', 'bar', '3', 4, 5.678], apcu_fetch('foo:bar3', $success));
        $this->assertSame(true, $success);

        $this->assertSame(true, $Storage->set('foo', 'bar4', null));
        $this->assertSame(null, apcu_fetch('foo:bar4', $success));
        $this->assertSame(true, $success);

        $this->assertSame(true, $Storage->set('foo', 'bar5', [null, true, false]));
        $this->assertSame([null, true, false], apcu_fetch('foo:bar5', $success));
        $this->assertSame(true, $success);

        $this->assertSame(true, $Storage->set('foo', 'bar6', ['foo' => 'bar', 'hello' => 'world']));
        $this->assertSame(['foo' => 'bar', 'hello' => 'world'], apcu_fetch('foo:bar6', $success));
        $this->assertSame(true, $success);

        $obj = (object) ['foo' => 'bar', 'hello' => 'world'];
        $this->assertSame(true, $Storage->set('foo', 'bar7', $obj));
        $result = apcu_fetch('foo:bar7', $success);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame(true, $result->foo === 'bar');
        $this->assertSame(true, $result->hello === 'world');
        $this->assertSame(true, $success);
    }

    /**
     * @see \Parallel\Storage\ApcuStorage::get
     */
    public function test_get() {
        $Storage = new ApcuStorage();
        $success = null;

        $this->assertSame(true, apcu_store('foo1:bar0', ''));
        $this->assertSame('', $Storage->get('foo1', 'bar0'));

        $this->assertSame(true, apcu_store('foo1:bar1', [1, 2, 3, 4, 5]));
        $this->assertSame([1, 2, 3, 4, 5], $Storage->get('foo1', 'bar1'));

        $this->assertSame(true, apcu_store('foo1:bar2', [true, false, null, '4', 5, 6.789, 'hello' => 'world']));
        $this->assertSame([true, false, null, '4', 5, 6.789, 'hello' => 'world'], $Storage->get('foo1', 'bar2'));

        $this->assertSame(true, apcu_store('foo1:bar3', 1));
        $this->assertSame(true, apcu_store('foo1:bar4', '2'));
        $this->assertSame(true, apcu_store('foo1:bar5', 3.14159265));
        $this->assertSame(true, apcu_store('foo1:bar6', 'hello world'));
        $this->assertSame(true, apcu_store('foo1:bar7', ['foo' => 'bar', 'hello' => 'world']));
        $this->assertSame(true, apcu_store('foo1:bar8', null));
        $this->assertSame(true, apcu_store('foo1:bar9', -42));
        $this->assertSame(true, apcu_store('foo1:bar10', "\x00\x10\x13"));

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
            'bar10' => "\x00\x10\x13",
        ], $Storage->get('foo1', ['bar3', 'bar4', 'bar5', 'bar6', 'bar7', 'bar8', 'bar9', 'bar10']));

        $this->assertSame(true, apcu_store('foo1:bar11', (object) ['a' => 1, 'b' => '2', 'c' => 'bar']));
        $result = $Storage->get('foo1', 'bar11');
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame(1, $result->a);
        $this->assertSame('2', $result->b);
        $this->assertSame('bar', $result->c);
    }


    /**
     * @see \Parallel\Storage\ApcuStorage::del
     */
    public function test_del() {
        $Storage = new ApcuStorage();
        $success = null;

        $this->assertSame(true, apcu_store('foo2:bar0', 'foo'));
        $this->assertSame('foo', apcu_fetch('foo2:bar0', $success));
        $this->assertSame(1, $Storage->del('foo2', 'bar0'));
        $this->assertSame(false, apcu_fetch('foo2:bar0', $success));
        $this->assertSame(false, $success);

        $this->assertSame(true, apcu_store('foo2:bar1', 'foo'));
        $this->assertSame(true, apcu_store('foo2:bar2', 'bar'));
        $this->assertSame(true, apcu_store('foo2:bar3', '123'));

        $this->assertSame('foo', apcu_fetch('foo2:bar1', $success));
        $this->assertSame('bar', apcu_fetch('foo2:bar2', $success));
        $this->assertSame('123', apcu_fetch('foo2:bar3', $success));

        $this->assertSame(2, $Storage->del('foo2', ['bar1', 'bar2', 'bar2']));

        $this->assertSame(false, apcu_fetch('foo2:bar1', $success));
        $this->assertSame(false, $success);
        $this->assertSame(false, apcu_fetch('foo2:bar2', $success));
        $this->assertSame(false, $success);
        $this->assertSame('123', apcu_fetch('foo2:bar3', $success));

        $this->assertSame(1, $Storage->del('foo2', ['bar1', 'bar3']));
        $this->assertSame(false, apcu_fetch('foo2:bar3', $success));
        $this->assertSame(false, $success);
    }


}
