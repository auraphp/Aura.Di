<?php
namespace Aura\Di;

class FakeResolveClass
{
    public $fake;
    public function __construct(FakeParentClass $fake)
    {
        $this->fake = $fake;
    }
}
