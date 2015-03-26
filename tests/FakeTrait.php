<?php
namespace Aura\Di;

trait FakeTrait
{
    use FakeChildTrait;

    protected $fake;

    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    public function getFake()
    {
        return $this->fake;
    }
}