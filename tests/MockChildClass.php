<?php
namespace aura\di;
class MockChildClass extends MockParentClass
{
    protected $zim;
    
    public function __construct($foo, MockOtherClass $zim)
    {
        parent::__construct($foo);
        $this->zim = $zim;
    }
}
