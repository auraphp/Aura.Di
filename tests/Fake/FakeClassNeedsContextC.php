<?php
namespace Aura\Di\Fake;

class FakeClassNeedsContextC
{
    public $fake;

    public function __construct(string $fake)
    {
        $this->fake = $fake;
    }
}