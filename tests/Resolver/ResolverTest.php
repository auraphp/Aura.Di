<?php
namespace Aura\Di\Resolver;

use Aura\Di\Injection\Lazy;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new Resolver(new Reflector());
    }

    public function testReadsConstructorDefaults()
    {
        $expect = ['foo' => new DefaultValueParam('foo', 'bar')];
        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $this->assertEquals($expect, $blueprint->getParams());
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
            'foo' => new DefaultValueParam('foo', 'bar'),
            'zim' => new DefaultValueParam('zim', null),
        ];

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $this->assertEquals($expect, $blueprint->getParams());
    }

    public function testHonorsExplicitParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'] = ['foo' => 'zim'];

        $expect = ['foo' => 'zim'];
        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeParentClass');
        $this->assertSame($expect, $blueprint->getParams());
    }

    public function testHonorsExplicitParentParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'] = ['foo' => 'dib'];

        $expect = [
            'foo' => 'dib',
            'zim' => new DefaultValueParam('zim', null),
        ];

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $this->assertEquals($expect, $blueprint->getParams());

        // for test coverage of the mock class
        $child = new \Aura\Di\Fake\FakeChildClass('bar', new \Aura\Di\Fake\FakeOtherClass);
    }

    public function testHonorsParentSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeParentClass']['setFake'] = 'fake1';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $blueprint->getSetters());

    }

    public function testHonorsOverrideSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeParentClass']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = 'fake2';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeChildClass');
        $expect = ['setFake' => 'fake2'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testHonorsTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeTrait']['setFake'] = 'fake1';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testHonorsChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeChildTrait']['setChildFake'] = 'fake1';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setChildFake' => 'fake1'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testHonorsGrandChildTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setGrandchildFake' => 'fake1'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testHonorsParentClassTraits()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeGrandchildTrait']['setGrandchildFake'] = 'fake1';
        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithParentTrait');
        $expect = ['setGrandchildFake' => 'fake1'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testHonorsOverrideTraitSetter()
    {
        $this->resolver->setters['Aura\Di\Fake\FakeTrait']['setFake'] = 'fake1';
        $this->resolver->setters['Aura\Di\Fake\FakeChildTrait']['setChildFake'] = 'fake2';
        $this->resolver->setters['Aura\Di\Fake\FakeClassWithTrait']['setFake'] = 'fake3';
        $this->resolver->setters['Aura\Di\Fake\FakeClassWithTrait']['setChildFake'] = 'fake4';

        $blueprint = $this->resolver->getUnified('Aura\Di\Fake\FakeClassWithTrait');
        $expect = ['setChildFake' => 'fake4', 'setFake' => 'fake3'];
        $this->assertSame($expect, $blueprint->getSetters());
    }

    public function testReflectionOnMissingClass()
    {
        $this->expectException('ReflectionException');
        $this->resolver->resolve(new Blueprint('NoSuchClass'));
    }

    public function testHonorsLazyParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass']['foo'] = new Lazy(function () {
            return new \Aura\Di\Fake\FakeOtherClass();
        });
        $actual = $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeParentClass'));
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getFoo());
    }

    public function testMissingParam()
    {
        $this->expectException('Aura\Di\Exception\MissingParam');
        $this->expectExceptionMessage('Aura\Di\Fake\FakeResolveClass::$fake');
        $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeResolveClass'));
    }

    public function testUnresolvedParamAfterMergeParams()
    {
        $this->expectException('Aura\Di\Exception\MissingParam');
        $this->resolver->resolve(
            new Blueprint(
                'Aura\Di\Fake\FakeParamsClass',
                ['noSuchParam' => 'foo']
            )
        );
    }

    public function testPositionalParams()
    {
        $this->resolver->params['Aura\Di\Fake\FakeParentClass'][0] = 'val0';
        $this->resolver->params['Aura\Di\Fake\FakeChildClass'][1] = 'val1';

        $actual = $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeChildClass'));
        $expect = [
            'foo' => 'val0',
            'zim' => 'val1',
        ];
        $this->assertSame($expect, ['foo' => $actual->getFoo(), 'zim' => $actual->getZim()]);
    }
}
