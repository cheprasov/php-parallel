<?php

namespace Parallel;

use Parallel\Storage\StorageInterface;

class Parallel {

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
     * Wait fork by names
     * @param string|string[] $names
     * @return array
     */
    public function wait($names) {
        $namesArr = (array) $names;
        foreach ($namesArr as $name) {
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
