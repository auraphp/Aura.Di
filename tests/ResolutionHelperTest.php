<?php
namespace Aura\Di;

use PHPUnit\Framework\TestCase;

class ResolutionHelperTest extends TestCase
{
    protected $container;

    protected $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = new ResolutionHelper($this->container);
    }

    protected function containerHas($spec, $instance)
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with($spec)
            ->will($this->returnValue(true));

        $this->container
            ->expects($this->never())
            ->method('newInstance');

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($spec)
            ->will($this->returnValue($instance));
    }

    protected function containerDoesNotHave($spec, $instance)
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with($spec)
            ->will($this->returnValue(false));

        $this->container
            ->expects($this->once())
            ->method('newInstance')
            ->with($spec)
            ->will($this->returnValue($instance));

        $this->container
            ->expects($this->never())
            ->method('get');
    }


    public function testResolveStringService()
    {
        $spec   = 'foo';
        $expect = new \stdClass();

        $this->containerHas($spec, $expect);

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

    public function testResolveStringInstance()
    {
        $spec   = 'foo';
        $expect = new \stdClass();

        $this->containerDoesNotHave($spec, $expect);

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }


    public function testResolveArrayService()
    {
        $class  = 'foo';
        $spec   = [$class, 'bar'];
        $return = new \stdClass();
        $expect = [$return, 'bar'];

        $this->containerHas($class, $return);

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

    public function testResolveArrayInstance()
    {
        $class  = 'foo';
        $spec   = [$class, 'bar'];
        $return = new \stdClass();
        $expect = [$return, 'bar'];

        $this->containerDoesNotHave($class, $return);

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

    public function testNoResolveObject()
    {
        $spec   = (object) ['foo' => 'bar'];
        $expect = $spec;

        $this->container
            ->expects($this->never())
            ->method('newInstance');

        $this->container
            ->expects($this->never())
            ->method('get');

        $this->assertEquals(
            $expect, call_user_func($this->helper, $spec)
        );
    }

}
