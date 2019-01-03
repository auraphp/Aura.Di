<?php
namespace Aura\Di\Fake;

class FakeChildClassWithDefaultParam extends FakeClassWithDefaultParam
{
    public $first;

    public $second;

    public function __construct($first = 1, $second = 3)
    {
        parent::__construct($first, $second);
    }
}
