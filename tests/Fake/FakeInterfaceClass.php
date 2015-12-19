<?php
namespace Aura\Di\Fake;

class FakeInterfaceClass implements FakeInterface
{
    protected $foo;

    protected $multi;

    public function setFoo($foo)
    {
        $this->foo = $foo;
        return $this;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function setMulti($arg1, $arg2, $arg3)
    {
        $this->multi = func_get_args();
    }

    public function getMulti()
    {
        return $this->multi;
    }
}
