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
namespace Parallel;

use Parallel\Storage\StorageInterface;

class Parallel {

    const VERSION = '1.2.0';

    /**
     * @var StorageInterface
     */
    protected $Storage;

    /**
     * @var array
     */
    protected $pids = [];

    /**
     * @var string
     */
    protected $key;

    /**
     * @param StorageInterface $Storage
     */
    public function __construct(StorageInterface $Storage) {
        $this->Storage = $Storage;
        $this->setKey(uniqid(posix_getpid() .'-', microtime(true)) .'-'. mt_rand(1, 9999));
    }

    /**
     * Run a new fork with some name
     * @param string $name
     * @param \Closure|string|array $callback
     * @return bool
     */
    public function run($name, $callback) {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('$callback is not callable');
        }

        // Because, we need to remove exists Instance before fork
        $this->Storage->setup();

        $pid = pcntl_fork();
        if ($pid === -1) {
            return false;
        }
        if ($pid) {
            // main fork
            $this->pids[$name] = $pid;
            return true;
        } else {
            $result = call_user_func($callback);
            $this->Storage->set($this->getKey(), $name, $result);
            exit(0);
        }
    }

    /**
     * Wait fork by names or all (without parameters)
     * @param string|string[]|null $names
     * @return array
     */
    public function wait($names = null) {
        $namesArr = !isset($names) ? array_keys($this->pids) : (array) $names;
        foreach ($namesArr as $name) {
            if (!isset($this->pids[$name])) {
                continue;
            }
            pcntl_waitpid($this->pids[$name], $status);
            unset($this->pids[$name]);
        }
        $result = $this->Storage->get($this->key, $namesArr);
        $this->Storage->del($this->key, $namesArr);
        return is_string($names) ? $result[$names] : $result;
    }

    /**
     * @param string $key
     */
    protected function setKey($key) {
        $this->key = $key;
    }

    /**
     * @return string
     */
    protected function getKey() {
        return $this->key;
    }

}
