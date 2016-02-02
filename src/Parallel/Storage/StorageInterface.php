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

interface StorageInterface {

    /**
     * @return void
     */
    public function setup();

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     * @param int $expire
     * @return bool
     */
    public function set($key, $field, $value, $expire = 0);

    /**
     * @param string $key
     * @param string|string[] $fields
     * @return array
     */
    public function get($key, $fields);

    /**
     * @param string $key
     * @param string|string[] $fields
     * @return int Count of deleted fields
     */
    public function del($key, $fields);

}
