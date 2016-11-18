<?php
namespace Aura\Di;

use Aura\Di\ResolutionHelper;
use Aura\Di\Injection\InjectionFactory;

class ResolutionHelperTest extends \PHPUnit_Framework_TestCase
{
    protected $injectionFactory;

    protected $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->injectionFactory = $this->getMockBuilder(InjectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = new ResolutionHelper($this->injectionFactory);
    }


    public function testResolveString()
    {
        $spec   = 'foo';
        $expect = 'return';

        $this->injectionFactory
            ->expects($this->once())
            ->method('newInstance')
            ->with($spec)
            ->will($this->returnValue($expect));

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

    public function testResolveArray()
    {
        $class  = 'foo';
        $spec   = [$class, 'bar'];
        $return = 'return';
        $expect = [$return, 'bar'];

        $this->injectionFactory
            ->expects($this->once())
            ->method('newInstance')
            ->with($class)
            ->will($this->returnValue($return));

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

    public function testNoResolveObject()
    {
        $spec   = (object) ['foo' => 'bar'];
        $expect = $spec;

        $this->injectionFactory
            ->expects($this->never())
            ->method('newInstance');

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

}
