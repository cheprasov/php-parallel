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

    /**
     * @param \Memcached|null $Memcached
     */
    public function setMemcached(\Memcached $Memcached = null) {
        $this->Memcached = $Memcached;
    }

    /**
     * @return \Memcached
     */
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
     * @param string $key
     * @return string
     */
    protected function getFieldFromKey($key) {
        $data = explode(':', $key, 2);
        return end($data);
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
        $serialized = $this->serialize($value);
        return $this->getMemcached()->set($this->getKeyByField($key, $field), $serialized, $expire ?: null);
    }

    /**
     * @inheritdoc
     */
    public function get($key, $fields) {
        if (is_string($fields)) {
            $data = $this->getMemcached()->get($this->getKeyByField($key, $fields));
            return $this->unserialize($data);
        }
        $data = $this->getMemcached()->getMulti(array_map(function($field) use ($key) {
            return $this->getKeyByField($key, $field);
        }, $fields));
        $result = [];
        foreach ($data as $key => $value) {
            $result[$this->getFieldFromKey($key)] = $this->unserialize($value);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function del($key, $fields) {
        if (is_string($fields)) {
            return (int) $this->getMemcached()->delete($this->getKeyByField($key, $fields));
        }
        // Because method <deleteMulti> does not work well
        $count = 0;
        foreach ($fields as $field) {
            $count += (int) $this->getMemcached()->delete($this->getKeyByField($key, $field));
        }
        return $count;
    }

    /**
     * @param mixed $data
     * @return string
     */
    protected function serialize($data) {
        return json_encode($data);
    }

    /**
     * @param string $data
     * @return mixed
     */
    protected function unserialize($data) {
        return json_decode($data, true);
    }

}
