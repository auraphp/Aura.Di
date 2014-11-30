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

    protected function usesTraits()
    {
        if (!function_exists('class_uses')) {
            $this->markTestSkipped("No traits before PHP 5.4");
        }
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
        $this->assertSame($expect, $actual_params);
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
        $this->assertSame($expect, $actual_params);

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
        $this->usesTraits();

        $this->factory->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsChildTraitSetter()
    {
        $this->usesTraits();

        $this->factory->setter['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsGrandChildTraitSetter()
    {
        $this->usesTraits();

        $this->factory->setter['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->factory->getUnified(
            'Aura\Di\FakeClassWithTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsParentClassTraits()
    {
        $this->usesTraits();

        $this->factory->setter['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';
        list($actual_config, $actual_setter) = $this->factory->getUnified(
            'Aura\Di\FakeClassWithParentTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsOverrideTraitSetter()
    {
        $this->usesTraits();

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

    public function testAutoResolveImplicit()
    {
        $object = $this->factory->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeParentClass', $object->fake);
    }

    public function testAutoResolveExplicit()
    {
        $this->factory->types['Aura\Di\FakeParentClass'] = $this->factory->newLazyNew('Aura\Di\FakeChildClass');
        $object = $this->factory->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeChildClass', $object->fake);
    }

    public function testAutoResolveArrayAndNull()
    {
        $object = $this->factory->newInstance('Aura\Di\FakeParamsClass');
        $this->assertSame(array(), $object->array);
        $this->assertNull($object->empty);
    }

    public function testAutoResolveDisabled()
    {
        $this->factory->setAutoResolve(false);
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\FakeResolveClass::$fake'
        );
        $this->factory->newInstance('Aura\Di\FakeResolveClass');
    }
}
