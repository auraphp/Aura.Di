<?php
namespace Aura\Di\Fake;

class FakeParentClass
{
    protected $foo;

    protected $multiSet = [];

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function mirror($value)
    {
        return $value;
    }

    public function multiSet($arg1, $arg2, $arg3)
    {
        $this->multiSet = func_get_args();
    }

    public function getMultiSet()
    {
        return $this->multiSet;
    }
}
