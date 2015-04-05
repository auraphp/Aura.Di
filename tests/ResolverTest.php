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
        $this->resolver->setters['Aura\Di\FakeParentClass']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);

    }

    public function testHonorsOverrideSetter()
    {
        $this->resolver->setters['Aura\Di\FakeParentClass']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\FakeChildClass']['setFake'] = 'fake2';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsTraitSetter()
    {
        $this->resolver->setters['Aura\Di\FakeTrait']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsGrandChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->resolver->getUnified(
            'Aura\Di\FakeClassWithTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsParentClassTraits()
    {
        $this->resolver->setters['Aura\Di\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';
        list($actual_config, $actual_setter) = $this->resolver->getUnified(
            'Aura\Di\FakeClassWithParentTrait'
        );
        $expect = array('setGrandchildFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
    }

    public function testHonorsOverrideTraitSetter()
    {
        $this->resolver->setters['Aura\Di\FakeTrait']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\FakeChildTrait']['setChildFake'] = 'fake2';
        $this->resolver->setters['Aura\Di\FakeClassWithTrait']['setFake'] = 'fake3';
        $this->resolver->setters['Aura\Di\FakeClassWithTrait']['setChildFake'] = 'fake4';

        list($actual_config, $actual_setter) = $this->resolver->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setChildFake' => 'fake4', 'setFake' => 'fake3');
        $this->assertSame($expect, $actual_setter);
    }

    public function testReflectionFailure()
    {
        $this->setExpectedException('Aura\Di\Exception\ReflectionFailure');
        $this->resolver->resolve('NoSuchClass');
    }

    public function testHonorsLazyParams()
    {
        $this->resolver->params['Aura\Di\FakeParentClass']['foo'] = new Lazy(function () {
            return new FakeOtherClass();
        });
        $actual = $this->resolver->resolve('Aura\Di\FakeParentClass');
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->params['foo']);
    }

    public function testMissingParam()
    {
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\FakeResolveClass::$fake'
        );
        $this->resolver->resolve('Aura\Di\FakeResolveClass');
    }
}
