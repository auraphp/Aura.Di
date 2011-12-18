<?php
namespace Aura\Di;
class MockParentClass
{
    protected $foo;

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
