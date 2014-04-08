<?php
namespace Aura\Di;
class MockInterfaceClass implements MockInterface
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
        return $this;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
