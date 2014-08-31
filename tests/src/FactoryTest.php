<?php
namespace Aura\Di;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new Factory;
    }

    public function testReadsConstructorDefaults()
    {
        $expect = array('foo' => 'bar');
        list($actual_params, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testTwiceForMerge()
    {
        $expect = $this->factory->getUnified('Aura\Di\FakeParentClass');
        $actual = $this->factory->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual);
    }

    public function testHonorsParentParams()
    {
        $expect = array(
            'foo' => 'bar',
            'zim' => null,
        );

        list($actual_params, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect['foo'], $actual_params['foo']);
    }

    public function testHonorsExplicitParams()
    {
        $this->factory->params['Aura\Di\FakeParentClass'] = array('foo' => 'zim');

        $expect = array('foo' => 'zim');
        list($actual_params, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testHonorsExplicitParentParams()
    {
        $this->factory->params['Aura\Di\FakeParentClass'] = array('foo' => 'dib');

        $expect = array(
            'foo' => 'dib',
            'zim' => null,
        );

        list($actual_params, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect['foo'], $actual_params['foo']);

        // for test coverage of the mock class
        $child = new \Aura\Di\FakeChildClass('bar', new \Aura\Di\FakeOtherClass);
    }

    public function testHonorsParentSetter()
    {
        $this->factory->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);

    }

    public function testHonorsOverrideSetter()
    {
        $this->factory->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';
        $this->factory->setter['Aura\Di\FakeChildClass']['setFake'] = 'fake2';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsTraitSetter()
    {
        if (!function_exists('class_uses')) {
            $this->markTestSkipped("No traits before PHP 5.4");
        }

        $this->factory->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsChildTraitSetter()
    {
        if (!function_exists('class_uses')) {
            $this->markTestSkipped("No traits before PHP 5.4");
        }

        $this->factory->setter['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsOverrideTraitSetter()
    {
        if (!function_exists('class_uses')) {
            $this->markTestSkipped("No traits before PHP 5.4");
        }

        $this->factory->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';
        $this->factory->setter['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake2';
        $this->factory->setter['Aura\Di\FakeClassWithTrait']['setFake'] = 'fake3';
        $this->factory->setter['Aura\Di\FakeClassWithTrait']['setChildFake'] = 'fake4';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake4', 'setFake' => 'fake3');
        $this->assertSame($expect, $actual_setter);
    }

    public function testReflectionFailure()
    {
        $this->setExpectedException('Aura\Di\Exception\ReflectionFailure');
        $this->factory->newInstance('NoSuchClass');
    }

    public function testHonorsLazyParams()
    {
        $this->factory->params['Aura\Di\FakeParentClass']['foo'] = $this->factory->newLazyNew('Aura\Di\FakeOtherClass');
        $object = $this->factory->newInstance('Aura\Di\FakeParentClass');
        $actual = $object->getFoo();
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual);
    }

    public function testAutoResolveExplicit()
    {
        $this->factory->resolve['Aura\Di\FakeParentClass'] = $this->factory->newLazyNew('Aura\Di\FakeChildClass');
        $object = $this->factory->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeChildClass', $object->fake);
    }

    public function testAutoResolveNewInstance()
    {
        $object = $this->factory->newInstance('Aura\Di\FakeChildClass');
        $this->assertSame('bar', $object->getFoo());
        $this->assertNull($object->getFake());
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $object->getZim());
    }

    public function testAutoResolveArrayAndNull()
    {
        $object = $this->factory->newInstance('Aura\Di\FakeParamsClass');
        $this->assertSame(array(), $object->array);
        $this->assertNull($object->empty);
    }
}
