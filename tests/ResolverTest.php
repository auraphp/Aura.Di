<?php
namespace Aura\Di;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new Resolver(new Reflector());
    }

    public function testReadsConstructorDefaults()
    {
        $expect = array('foo' => 'bar');
        list($actual_params, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testTwiceForMerge()
    {
        $expect = $this->resolver->getUnified('Aura\Di\FakeParentClass');
        $actual = $this->resolver->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual);
    }

    public function testHonorsParentParams()
    {
        $expect = array(
            'foo' => 'bar',
            'zim' => null,
        );

        list($actual_params, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testHonorsExplicitParams()
    {
        $this->resolver->params['Aura\Di\FakeParentClass'] = array('foo' => 'zim');

        $expect = array('foo' => 'zim');
        list($actual_params, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testHonorsExplicitParentParams()
    {
        $this->resolver->params['Aura\Di\FakeParentClass'] = array('foo' => 'dib');

        $expect = array(
            'foo' => 'dib',
            'zim' => null,
        );

        list($actual_params, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect, $actual_params);

        // for test coverage of the mock class
        $child = new \Aura\Di\FakeChildClass('bar', new \Aura\Di\FakeOtherClass);
    }

    public function testHonorsParentSetter()
    {
        $this->resolver->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);

    }

    public function testHonorsOverrideSetter()
    {
        $this->resolver->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';
        $this->resolver->setter['Aura\Di\FakeChildClass']['setFake'] = 'fake2';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsTraitSetter()
    {
        $this->resolver->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsChildTraitSetter()
    {
        $this->resolver->setter['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsGrandChildTraitSetter()
    {
        $this->resolver->setter['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified(
            'Aura\Di\FakeClassWithTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsParentClassTraits()
    {
        $this->resolver->setter['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';
        list($actual_config, $actual_setter) = $this->resolver->getUnified(
            'Aura\Di\FakeClassWithParentTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsOverrideTraitSetter()
    {
        $this->resolver->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';
        $this->resolver->setter['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake2';
        $this->resolver->setter['Aura\Di\FakeClassWithTrait']['setFake'] = 'fake3';
        $this->resolver->setter['Aura\Di\FakeClassWithTrait']['setChildFake'] = 'fake4';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake4', 'setFake' => 'fake3');
        $this->assertSame($expect, $actual_setter);
    }

    public function testReflectionFailure()
    {
        $this->setExpectedException('Aura\Di\Exception\ReflectionFailure');
        $this->resolver->newInstance('NoSuchClass');
    }

    public function testHonorsLazyParams()
    {
        $this->resolver->params['Aura\Di\FakeParentClass']['foo'] = new LazyNew($this->resolver, 'Aura\Di\FakeOtherClass');
        $object = $this->resolver->newInstance('Aura\Di\FakeParentClass');
        $actual = $object->getFoo();
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual);
    }

    public function testAutoResolveImplicit()
    {
        $this->resolver->setAutoResolve(true);
        $object = $this->resolver->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeParentClass', $object->fake);
    }

    public function testAutoResolveExplicit()
    {
        $this->resolver->setAutoResolve(true);
        $this->resolver->types['Aura\Di\FakeParentClass'] = new LazyNew($this->resolver, 'Aura\Di\FakeChildClass');
        $object = $this->resolver->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeChildClass', $object->fake);
    }

    public function testAutoResolveArrayAndNull()
    {
        $this->resolver->setAutoResolve(true);
        $object = $this->resolver->newInstance('Aura\Di\FakeParamsClass');
        $this->assertSame(array(), $object->array);
        $this->assertNull($object->empty);
    }

    public function testAutoResolveDisabled()
    {
        $this->resolver->setAutoResolve(false);
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\FakeResolveClass::$fake'
        );
        $this->resolver->newInstance('Aura\Di\FakeResolveClass');
    }
}
