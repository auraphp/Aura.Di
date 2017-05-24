<?php
namespace Aura\Di\Fake;

class FakeResolveClass
{
    public $fake;
    public function __construct(FakeParentClass $fake)
    {
        $this->fake = $fake;
    }

    public function getParentClass()
    {
        return $this->fake;
    }
}
