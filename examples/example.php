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
require (dirname(__DIR__).'/vendor/autoload.php');

use Parallel\Parallel;

// EXAMPLE, how run parallel 3 operations.

// Using Parallel via ApcStorage (APCu, see http://php.net/manual/ru/book.apcu.php)
$Parallel = new Parallel(new \Parallel\Storage\ApcStorage());

// if you have not APCu, you can use Memcached as Storage.
// Note: you can't store object in Memcached
//    $Parallel = new Parallel(new \Parallel\Storage\MemcachedStorage([
//        'servers' => [['127.0.0.1', 11211]]
//    ]));

$time = microtime(true);

// 1st operation
$Parallel->run('foo', function() {
    sleep(2);
    return ['hello' => 'world'];
});

// 2nd operation
$Parallel->run('obj', function() {
    sleep(2);
    return (object) ['a' => 1, 'b' => 2, 'c' => 3];
});

// 3th operation
// do some thing ...

// waiting for <foo> and <obj>
// and get results
$result = $Parallel->wait(['foo', 'obj']);
print_r($result);
print_r(microtime(true) - $time);
//    Array
//    (
//        [foo] => Array
//            (
//                [hello] => world
//            )
//
//        [obj] => stdClass Object
//            (
//                [a] => 1
//                [b] => 2
//                [c] => 3
//            )
//    )
//    2.0130307674408

