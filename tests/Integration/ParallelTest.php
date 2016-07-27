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
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;
use Parallel\Storage\MemcachedStorage;
use Parallel\Storage\RedisStorage;

/**
 * @see \Parallel\Parallel
 */
class ParallelTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function getStorage() {
        return [
            [function() {return new ApcuStorage();}],
            [function() {return new MemcachedStorage(['servers' => [explode(':', TEST_MEMCACHED_SERVER)]]);}],
            [function() {return
                new RedisStorage(['server' => TEST_REDIS_SERVER]);}],
        ];
    }

    /**
     * @dataProvider getStorage
     *
     * @param \Closure $getStorage
     */
    public function test_parallelWithStorage(\Closure $getStorage) {
        $Parallel = new Parallel($getStorage());

        $time = microtime(true);
        for ($i = 1; $i <= 5; ++$i) {
            $Parallel->run('n:'. $i, function() use ($i) {
                sleep($i);
                return $i * $i;
            });
        }
        sleep(4);
        $result = $Parallel->wait(['n:1', 'n:2', 'n:3', 'n:4', 'n:5']);
        $time = microtime(true) - $time;
        $this->assertGreaterThanOrEqual(5, $time);
        $this->assertLessThan(6, $time);

        $this->assertSame(['n:1' => 1, 'n:2' => 4, 'n:3' => 9, 'n:4' => 16, 'n:5' => 25], $result);
    }

    /**
     * @dataProvider getStorage
     *
     * @param \Closure $getStorage
     */
    public function test_nestedParallelWithStorage(\Closure $getStorage) {
        $Parallel = new Parallel($getStorage());

        $time = microtime(true);
        for ($i = 1; $i <= 2; ++$i) {
            $Parallel->run('n:'. $i, function() use ($getStorage) {
                $Parallel = new Parallel($getStorage());
                $Parallel->run('n:1', function() {
                    sleep(2);
                    return 'foo';
                });
                $Parallel->run('n:2', function() {
                    sleep(2);
                    return 'bar';
                });
                // wait all
                return $Parallel->wait();
            });
        }
        sleep(2);
        $result = $Parallel->wait(['n:1', 'n:2']);
        $time = microtime(true) - $time;
        $this->assertGreaterThanOrEqual(2, $time);
        $this->assertLessThan(3, $time);

        $this->assertSame(
            ['n:1' => ['n:1' => 'foo', 'n:2' => 'bar'], 'n:2' => ['n:1' => 'foo', 'n:2' => 'bar']],
            $result
        );
    }

}
