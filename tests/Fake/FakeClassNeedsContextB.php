<?php
namespace Aura\Di\Fake;

class FakeClassNeedsContextB
{
    public $fake;

    public function __construct(FakeClassNeedsContextC $fake)
    {
        $this->fake = $fake;
    }
}