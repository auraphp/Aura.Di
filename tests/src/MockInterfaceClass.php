<?php
namespace Aura\Di;
class MockInterfaceClass implements MockInterface
{
    protected $_foo;

    public function setFoo($foo)
    {
        $this->_foo = $foo;
        return $this;
    }

    public function getFoo()
    {
        return $this->_foo;
    }
}
