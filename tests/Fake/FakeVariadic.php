<?php
namespace Aura\Di\Fake;

class FakeVariadic
{
    protected $foo;
    protected $items;

    public function __construct($foo, \stdClass ...$items)
    {
        $this->foo = $foo;
        $this->items = $items;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getItems()
    {
        return $this->items;
    }
}
