<?php
namespace Aura\Di\Fake;

trait FakeChildTrait
{
    use FakeGrandchildTrait;

    protected $child_fake;

    protected $child_multi_fake;

    public function setChildFake($fake)
    {
        $this->child_fake = $fake;
    }

    public function getChildFake()
    {
        return $this->child_fake;
    }

    public function setChildMultiFake($arg1, $arg2, $arg3)
    {
        $this->child_multi_fake = func_get_args();
    }

    public function getChildMultiFake()
    {
        return $this->child_multi_fake;
    }
}
