<?php
namespace Aura\Di\Fake;

class FakeClassNeedsContextA
{
    public $fake;

    public function __construct(FakeClassNeedsContextB $fake)
    {
        $this->fake = $fake;
    }
}