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

use RedisClient\ClientFactory;
use RedisClient\RedisClient;

class RedisStorage implements StorageInterface {

    /**
     * @var RedisClient
     */
    protected $Redis;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->options = $options;
    }

    /**
     * @param RedisClient|null $Redis
     */
    public function setRedis(RedisClient $Redis = null) {
        $this->Redis = $Redis;
    }

    /**
     * @return RedisClient
     */
    public function getRedis() {
        if (!$this->Redis) {
            $this->Redis = ClientFactory::create($this->options);
        }
        return $this->Redis;
    }

    /**
     * @inheritdoc
     */
    public function setup() {
        $this->setRedis(null);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $field, $value, $expire = 0) {
        $serialized = $this->serialize($value);
        $result = $this->getRedis()->hset($key, $field, $serialized);
        if ($expire) {
            $this->getRedis()->expire($key, $expire);
        }
        return (bool) $result;
    }

    /**
     * @inheritdoc
     */
    public function get($key, $fields) {
        if (is_string($fields)) {
            $data = $this->getRedis()->hget($key, $fields);
            return $this->unserialize($data);
        }
        $result = array_combine($fields, $this->getRedis()->hmget($key, $fields));
        foreach ($result as $field => $value) {
            $result[$field] = $this->unserialize($value);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function del($key, $fields) {
        return $this->getRedis()->hdel($key, (array) $fields);
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
