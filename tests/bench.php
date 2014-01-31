<?php
class Foo {
    public function __construct($param = null) {}
}

class Bar extends Foo {}

class Baz extends Bar {}

class Dib extends Baz {}

class Zim extends Dib {}

class Gir extends Zim {}

$di = require dirname(__DIR__) . '/scripts/instance.php';
$k = 10000;

$before = microtime(true);
for ($i = 0; $i < $k; $i ++) {
    $gir = $di->newInstance('Gir');
}
$after = microtime(true);
echo $after - $before . PHP_EOL;
