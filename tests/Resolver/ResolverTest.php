<?php
namespace Aura\Di\Resolver;

use Aura\Di\Injection\Lazy;

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
        $expect = ['foo' => 'bar'];
        list($actual_params, $actual_setters) = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testTwiceForMerge()
    {
        $expect = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $actual = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $this->assertSame($expect, $actual);
    }

    public function testHonorsParentParams()
    {
        $expect = [
            'foo' => 'bar',
            'zim' => null,
        ];

        list($actual_params, $actual_setters) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testHonorsExplicitParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'] = ['foo' => 'zim'];

        $expect = ['foo' => 'zim'];
        list($actual_params, $actual_setters) = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testHonorsExplicitParentParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'] = ['foo' => 'dib'];

        $expect = [
            'foo' => 'dib',
            'zim' => null,
        ];

        list($actual_params, $actual_setters) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $this->assertSame($expect, $actual_params);

        // for test coverage of the mock class
        $child = new \Aura\Di\Fake\FakeChildClass('bar', new \Aura\Di\Fake\FakeOtherClass);
    }

    public function testHonorsParentSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeParentClass']['setFake'] = 'fake1';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsOverrideSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeParentClass']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = 'fake2';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['setFake' => 'fake2'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeTrait']['setFake'] = 'fake1';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeChildTrait']['setChildFake'] = 'fake1';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setChildFake' => 'fake1'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsGrandChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified(
            'Aura\Di\Fake\FakeClassWithTrait'
        );
        $expect = ['setGrandchildFake' => 'fake1'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsParentClassTraits()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';
        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified(
            'Aura\Di\Fake\FakeClassWithParentTrait'
        );
        $expect = ['setGrandchildFake' => 'fake1'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testHonorsOverrideTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeTrait']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\Fake\FakeChildTrait']['setChildFake'] = 'fake2';
        $this->resolver->setters['Aura\Di\Fake\FakeClassWithTrait']['setFake'] = 'fake3';
        $this->resolver->setters['Aura\Di\Fake\FakeClassWithTrait']['setChildFake'] = 'fake4';

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setChildFake' => 'fake4', 'setFake' => 'fake3'];
        $this->assertSame($expect, $actual_setters);
    }

    public function testReflectionOnMissingClass()
    {
        $this->setExpectedException('ReflectionException');
        $this->resolver->resolve('NoSuchClass');
    }

    public function testHonorsLazyParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass']['foo'] = new Lazy(function () {
            return new \Aura\Di\Fake\FakeOtherClass();
        });
        $actual = $this->resolver->resolve('Aura\Di\Fake\FakeParentClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->params['foo']);
    }

    public function testMissingParam()
    {
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\Fake\FakeResolveClass::$fake'
        );
        $this->resolver->resolve('Aura\Di\Fake\FakeResolveClass');
    }

    public function testUnresolvedParamAfterMergeParams()
    {
        $this->setExpectedException('Aura\Di\Exception\MissingParam');
        $this->resolver->resolve('Aura\Di\Fake\FakeParamsClass', [
            'noSuchParam' => 'foo'
        ]);
    }

    public function testPositionalParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'][0] = 'val0';
        $this->resolver->params['Aura\Di\Fake\FakeChildClass'][1] = 'val1';

        $actual = $this->resolver->resolve('Aura\Di\Fake\FakeChildClass');
        $expect = [
            'foo' => 'val0',
            'zim' => 'val1',
        ];
        $this->assertSame($expect, $actual->params);
    }

    public function testHonorsParentMethod()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeParentClass']['multiSet'] = ['fake1', 'fake2', 'fake3'];

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['multiSet' => ['fake1', 'fake2', 'fake3']];
        $this->assertSame($expect, $actual_methods);
    }

    public function testHonorsOverrideMethod()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeParentClass']['multiSet'] = ['fake1', 'fake2', 'fake3'];
        $this->resolver->methods['Aura\Di\Fake\FakeChildClass']['multiSet'] = ['fake4', 'fake5', 'fake6'];

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['multiSet' => ['fake4', 'fake5', 'fake6']];
        $this->assertSame($expect, $actual_methods);
    }

    public function testHonorsTraitMethod()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeTrait']['setMultiFake'] = ['fake1', 'fake2', 'fake3'];

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setMultiFake' => ['fake1', 'fake2', 'fake3']];
        $this->assertSame($expect, $actual_methods);
    }

    public function testHonorsChildTraitMethod()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeChildTrait']['setChildMultiFake'] = ['fake4', 'fake5', 'fake6'];

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setChildMultiFake' => ['fake4', 'fake5', 'fake6']];
        $this->assertSame($expect, $actual_methods);
    }

    public function testHonorsGrandChildTraitMethod()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildMultiFake'] = ['fake1', 'fake2', 'fake3'];

        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified(
            'Aura\Di\Fake\FakeClassWithTrait'
        );
        $expect = ['setGrandchildMultiFake' => ['fake1', 'fake2', 'fake3']];
        $this->assertSame($expect, $actual_methods);
    }

    public function testHonorsParentClassMethodTraits()
    {
        $this->resolver->methods['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildMultiFake'] = ['fake1', 'fake2', 'fake3'];
        list($actual_params, $actual_setters, $actual_methods) = $this->resolver->getUnified(
            'Aura\Di\Fake\FakeClassWithParentTrait'
        );
        $expect = ['setGrandchildMultiFake' => ['fake1', 'fake2', 'fake3']];
        $this->assertSame($expect, $actual_methods);
    }

}
