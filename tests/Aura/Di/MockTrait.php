<?php
namespace Aura\Di;

trait MockTrait
{
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