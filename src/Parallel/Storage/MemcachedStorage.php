<?php
/**
 * This file is part of RedisClient.
 * git: https://github.com/cheprasov/php-parallel
 *
 * (C) Alexander Cheprasov <cheprasov.84@ya.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Parallel\Storage;

class MemcachedStorage implements StorageInterface {

    /**
     * @var \Memcached
     */
    protected $Memcached;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct($options) {
        $this->options = $options;
    }

    public function setMemcached(\Memcached $Memcached = null) {
        $this->Memcached = $Memcached;
    }

    public function getMemcached() {
        if (!$this->Memcached) {
            $this->Memcached = new \Memcached();
            $this->Memcached->addServers($this->options['servers']);
        }
        return $this->Memcached;
    }

    /**
     * @param string $key
     * @param string $field
     * @return string
     */
    protected function getKeyByField($key, $field) {
        return $key.':'.$field;
    }

    /**
     * @inheritdoc
     */
    public function setup() {
        $this->setMemcached(null);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $field, $value, $expire = 0) {
        $this->getMemcached()->set($this->getKeyByField($key, $field), $value, $expire ?: null);
    }

    /**
     * @inheritdoc
     */
    public function get($key, $fields) {
        if (is_string($fields)) {
            return $this->getMemcached()->get($this->getKeyByField($key, $fields));
        }
        $result = $this->getMemcached()->getMulti(array_map(function($field) use ($key) {
            return $this->getKeyByField($key, $field);
        }, $fields));
        return array_combine($fields, array_values($result));
    }

    /**
     * @inheritdoc
     */
    public function del($key, $fields) {
        if (is_string($fields)) {
            return $this->getMemcached()->delete($this->getKeyByField($key, $fields));
        }
        return $this->getMemcached()->deleteMulti(array_map(function($field) use ($key) {
            return $this->getKeyByField($key, $field);
        }, $fields));
    }

}
