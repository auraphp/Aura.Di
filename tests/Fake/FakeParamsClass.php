<?php
namespace Aura\Di\Fake;

class FakeParamsClass
{
    public $array;
    public $empty = 'not null';
    public $items;
    public function __construct(array $array, $empty, \stdClass ...$items)
    {
        $this->array = $array;
        $this->empty = null;
        $this->items = $items;
    }
}
