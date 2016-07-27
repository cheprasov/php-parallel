# Parallel v1.2.0 for PHP >= 5.5

The class allows you to run multiple operations parallel in different processes and send results to the main process. Useful if you need to run multiple independent operations simultaneously, instead of sequential execution, or if you run several independent queries, for example, queries to different data bases.

## Communications

Communications of data between processes is via one (or more) of storages:

- APCu
- Memcached
- Redis

## Using

```php
<?php

require (dirname(__DIR__).'/vendor/autoload.php');

use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;

// EXAMPLE, how to run parallel 3 operations.

// Using Parallel via ApcuStorage (APCu, see http://php.net/manual/ru/book.apcu.php)
$Parallel = new Parallel(new ApcuStorage());

// if you have not APCu, you can use Memcached or Redis as Storage.
// Note: you can't store objects in Memcached or Redis and you can't store binary strings (use <base64> functions)

//    $Parallel = new Parallel(new \Parallel\Storage\MemcachedStorage([
//        'servers' => [['127.0.0.1', 11211]]
//    ]));

//    $Parallel = new Parallel(new \Parallel\Storage\RedisStorage([
//        'server' => 'tcp://127.0.0.1:6379'
//    ]));

$time = microtime(true);

// 1st operation
$Parallel->run('foo', function() {
    // You can use Parallel inside run function by creating new objects Parallel.
    // Example: $Parallel = new Parallel(new \Parallel\Storage\ApcuStorage());
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
sleep(2);

// waiting for <foo> and <obj> and get results.
// use wait() without parameters for wait all forks. Example: $Parallel->wait();
$result = $Parallel->wait(['foo', 'obj']);

print_r($result);
print_r(microtime(true) - $time);
// 3 parallel operations by 2 seconds take about 2 seconds, instead 6 seconds.

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
```

## Installation

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar require cheprasov/php-parallel


## Running tests

To run tests type in console:

    ./vendor/bin/phpunit


## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
