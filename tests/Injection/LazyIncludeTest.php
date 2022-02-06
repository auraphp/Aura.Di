<?php
namespace Aura\Di\Injection;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class LazyIncludeTest extends TestCase
{
    public function test__invoke()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazyInclude = new LazyInclude($file);
        $actual = $lazyInclude->__invoke();
        $expected = ['foo' => 'bar'];
        $this->assertSame($expected, $actual);
    }
}
