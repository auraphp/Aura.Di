<?php
namespace Aura\Di;

trait FakeChildTrait
{
    protected $child_fake;

    public function setChildFake($fake)
    {
        $this->child_fake = $fake;
    }

    public function getChildFake()
    {
        return $this->child_fake;
    }
}