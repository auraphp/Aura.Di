<?php
namespace Aura\Di\Fake;

trait FakeGrandchildTrait
{
    protected $grandchild_fake;

    protected $grandchild_multi_fake;

    public function setGrandchildFake($fake)
    {
        $this->grandchild_fake = $fake;
    }

    public function getGrandchildFake()
    {
        return $this->grandchild_fake;
    }

    public function setGrandchildMultiFake($arg1, $arg2, $arg3)
    {
        $this->grandchild_multi_fake = func_get_args();
    }

    public function getGrandchildMultiFake()
    {
        return $this->grandchild_multi_fake;
    }
}
