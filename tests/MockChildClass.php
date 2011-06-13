<?php
namespace Aura\Di;
class MockChildClass extends MockParentClass
{
    protected $zim;
    
    protected $fake;
    
    public function __construct($foo, MockOtherClass $zim)
    {
        parent::__construct($foo);
        $this->zim = $zim;
    }
    
    public function setFake($fake)
    {
        $this->fake = $fake;
    }
    
    public function getFake()
    {
        return $this->fake;
    }
}
