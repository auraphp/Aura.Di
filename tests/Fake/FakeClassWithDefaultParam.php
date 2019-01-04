<?php
namespace Aura\Di\Fake;

class FakeClassWithDefaultParam
{
    public $first;
    public $second;

    public function __construct($first = 1, $second = 2)
    {
        $this->first = $first;
        $this->second = $second;
    }
}