<?php
namespace Aura\Di;

class FakeWithNoCtor
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
