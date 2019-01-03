<?php
namespace Aura\Di\Injection;

use PHPUnit\Framework\TestCase;

class LazyRequireTest extends TestCase
{
    public function test__invoke()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazyInclude = new LazyRequire($file);
        $actual = $lazyInclude->__invoke();
        $expected = ['foo' => 'bar'];
        $this->assertSame($expected, $actual);
    }
}
