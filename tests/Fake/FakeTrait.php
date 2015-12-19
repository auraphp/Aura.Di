<?php
namespace Aura\Di\Fake;

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

    public function setMultiFake($arg1, $arg2, $arg3)
    {
        $this->multiFake = func_get_args();
    }

    public function getMultiFake()
    {
        return $this->multiFake;
    }
}
