<?php
namespace Aura\Di\Fake;

class FakeClassWithDefaultParamInConstructor
{
    public $fake;

    public function __construct(FakeInterfaceClass $fake = null)
    {
        $this->fake = $fake;
    }
}