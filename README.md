# php-parallel
__in development__

__NOTE:__ You can use it, but I have not wrote tests for it yet.

The class allows you to run multiple operations in multiple threads. Useful if you need to run multiple independent operations simultaneously, instead of sequential execution. It is very useful if you run several independent queries, for example.

## Using

```php
<?php

require (dirname(__DIR__).'/vendor/autoload.php');

use Parallel\Parallel;

// EXAMPLE, how run parallel 3 operations.

// Using Parallel via ApcuStorage (APCu, see http://php.net/manual/ru/book.apcu.php)
$Parallel = new Parallel(new \Parallel\Storage\ApcuStorage());

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
sleep(2);

// waiting for <foo> and <obj>
// and get results
$result = $Parallel->wait(['foo', 'obj']);
print_r($result);
// 3 parallel operations by 2 seconds take about 2 seconds, instead 6 seconds.
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
```
