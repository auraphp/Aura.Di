<?php
namespace Aura\Di;

use Acclimate\Container\CompositeContainer;
use Aura\Di\Fake\FakeMutationClass;
use Aura\Di\Fake\FakeMutationWithDependencyClass;
use Aura\Di\Fake\FakeOtherClass;
use Aura\Di\Fake\FakeParamsClass;
use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Resolver\Blueprint;
use Aura\Di\Resolver\Reflector;
use Aura\Di\Resolver\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var Container|MockObject
     */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        $builder = new ContainerBuilder;
        $this->container = $builder->newInstance();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetInjectionFactory()
    {
        $actual = $this->container->getInjectionFactory();
        $expect = 'Aura\Di\Injection\InjectionFactory';
        $this->assertInstanceOf($expect, $actual);
    }

    public function testNewResolutionHelper()
    {
        $actual = $this->container->newResolutionHelper();
        $expect = 'Aura\Di\ResolutionHelper';
        $this->assertInstanceOf($expect, $actual);
    }

    public function testMagicGet()
    {
        $this->container->params['foo'] = 'bar';
        $this->container->setters['baz'] = 'dib';
        $this->container->setters['zim'] = 'gir';

        $expect = ['foo' => 'bar'];
        $this->assertSame($expect, $this->container->params);

        $expect = ['baz' => 'dib', 'zim' => 'gir'];
        $this->assertSame($expect, $this->container->setters);
    }

    public function testHasGet()
    {
        $expect = (object) [];
        $this->container->set('foo', $expect);

        $this->assertTrue($this->container->has('foo'));
        $this->assertFalse($this->container->has('bar'));

        $actual = $this->container->get('foo');
        $this->assertSame($expect, $actual);

        // get it again for coverage
        $again = $this->container->get('foo');
        $this->assertSame($actual, $again);
    }

    public function testGetNoSuchService()
    {
        $this->expectException('Aura\Di\Exception\ServiceNotFound');
        $this->container->get('foo');
    }

    public function testGetServiceInsideClosure()
    {
        $di = $this->container;
        $di->set('foo', function() use ($di) {
            return new \Aura\Di\Fake\FakeParentClass();
        });

        $actual = $this->container->get('foo');
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $actual);
    }

    public function testGetServicesAndInstances()
    {
        $this->container->set('foo', (object) []);
        $this->container->set('bar', (object) []);
        $this->container->set('baz', (object) []);

        $expect = ['foo', 'bar', 'baz'];
        $actual = $this->container->getServices();
        $this->assertSame($expect, $actual);

        $service = $this->container->get('bar');
        $expect = ['bar'];
        $actual = $this->container->getInstances();
        $this->assertSame($expect, $actual);
    }

    public function testLazyGet()
    {
        $this->container->set('foo', function() {
            return new \Aura\Di\Fake\FakeOtherClass();
        });

        $lazy = $this->container->lazyGet('foo');

        $this->assertInstanceOf('Aura\Di\Injection\LazyGet', $lazy);

        $foo = $lazy();

        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $foo);
    }

    public function testMagicGetNoSuchProperty()
    {
        $this->expectException('Aura\Di\Exception\NoSuchProperty');
        $this->container->no_such_property;
    }

    public function testNewInstanceWithDefaults()
    {
        $instance = $this->container->newInstance('Aura\Di\Fake\FakeParentClass');
        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testNewInstanceWithOverride()
    {
        $instance = $this->container->newInstance(
            'Aura\Di\Fake\FakeParentClass',
            ['foo' => 'dib']
        );

        $expect = 'dib';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testNewInstanceWithVariadic()
    {
        // Variadics are only available in PHP >= 5.6, and not in HHVM
        if (version_compare(PHP_VERSION, '5.6') === -1 || defined('HHVM_VERSION')) {
            $this->markTestSkipped();
        }
        $foo = 'bar';
        $items = [(object) ['id' => 1], (object) ['id' => 2]];
        $instance = $this->container->newInstance(
            'Aura\Di\Fake\FakeVariadic',
            ['foo' => $foo, 'items' => $items]
        );
        $this->assertSame($foo, $instance->getFoo());
        $this->assertSame($items, $instance->getItems());
    }

    public function testLazyNew()
    {
        $lazy = $this->container->lazyNew('Aura\Di\Fake\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\Injection\LazyNew', $lazy);
        $foo = $lazy();
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $foo);
    }

    public function testLazyNewWithVariadic()
    {
        // Variadics are only available in PHP >= 5.6, and not in HHVM
        if (version_compare(PHP_VERSION, '5.6') === -1 || defined('HHVM_VERSION')) {
            $this->markTestSkipped();
        }
        $foo = 'bar';
        $items = [(object) ['id' => 1], (object) ['id' => 2]];
        $lazy = $this->container->lazyNew(
            'Aura\Di\Fake\FakeVariadic',
            ['foo' => $foo, 'items' => $items]
        );
        $instance = $lazy();
        $this->assertSame($foo, $instance->getFoo());
        $this->assertSame($items, $instance->getItems());
    }

    public function testLockedMagicGet()
    {
        $this->container->lock();
        $this->expectException('Aura\Di\Exception\ContainerLocked');
        $this->container->params;
    }

    public function testLockedSet()
    {
        $this->container->lock();
        $this->expectException('Aura\Di\Exception\ContainerLocked');
        $this->container->set('foo', function() { return (object) []; });
    }

    public function testLazyInclude()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyInclude($file);
        $this->assertInstanceOf('Aura\Di\Injection\LazyInclude', $lazy);
        $actual = $lazy();
        $expect = ['foo' => 'bar'];
        $this->assertSame($expect, $actual);
    }

    public function testLazyRequire()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyRequire($file);
        $this->assertInstanceOf('Aura\Di\Injection\LazyRequire', $lazy);
        $actual = $lazy();
        $expect = ['foo' => 'bar'];
        $this->assertSame($expect, $actual);
    }

    public function testLazy()
    {
        $lazy = $this->container->lazy(
            $this->container->lazyNew('Aura\Di\Fake\FakeMeldingClass'),
            $this->container->lazyNew('Aura\Di\Fake\FakeMalleableClass', ['foo' => 'bar'])
        );

        $this->assertInstanceOf('Aura\Di\Injection\Lazy', $lazy);
        $meldingResult = $lazy();
        $this->assertInstanceOf('Aura\Di\Fake\FakeMalleableClass', $meldingResult);

        $actual = $meldingResult->getFoo();
        $expect = 'baz';
        $this->assertSame($expect, $actual);
    }

    public function testLazyWithArrayContainingLazy()
    {
        $lazy = $this->container->lazy(
            [$this->container->lazyNew('Aura\Di\Fake\FakeParentClass'), 'mirror'],
            $this->container->lazy(function () { return 'foo'; })
        );

        $this->assertInstanceOf('Aura\Di\Injection\Lazy', $lazy);
        $actual = $lazy();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testLazyArray()
    {
        $lazyArray = $this->container->lazyArray([
            $this->container->lazyNew('Aura\Di\Fake\FakeOtherClass'),
        ]);

        $this->assertInstanceOf('Aura\Di\Injection\LazyArray', $lazyArray);
        $actual = $lazyArray();
        $this->assertIsArray($actual);
        $this->assertArrayHasKey(0, $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual[0]);
    }

    public function testLazyArrayAppend()
    {
        $lazyArray = $this->container->lazyArray([]);
        $lazyArray->append($this->container->lazyNew('Aura\Di\Fake\FakeOtherClass'));

        $actual = $lazyArray();
        $this->assertIsArray($actual);
        $this->assertArrayHasKey(0, $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual[0]);
    }

    public function testLazyArrayAppendWithKey()
    {
        $lazyArray = $this->container->lazyArray([]);
        $lazyArray['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeOtherClass');

        $actual = $lazyArray();
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('fake', $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual['fake']);
    }

    public function testLazyArrayGetArrayCopy()
    {
        $lazyArray = $this->container->lazyArray([]);
        $lazyArray['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeOtherClass');

        $copy = $lazyArray->getArrayCopy();
        $this->assertIsArray($copy);
        $this->assertArrayHasKey('fake', $copy);
        $this->assertInstanceOf('Aura\Di\Injection\LazyNew', $copy['fake']);
    }

    public function testLazyCallable()
    {
        $lazyCallable = $this->container->lazyCallable($this->container->lazyNew('Aura\Di\Fake\FakeInvokableClass'));
        $callableRunner = function(callable $callable) {
            return $callable('baz');
        };

        $this->assertInstanceOf('Aura\Di\Injection\LazyCallable', $lazyCallable);
        $actual = $callableRunner($lazyCallable);
        $expect = 'barbaz';
        $this->assertSame($expect, $actual);
    }

    public function testLazyCallableWithArrayContainingLazy()
    {
        $this->container->set('invokable_class', $this->container->lazyNew('Aura\Di\Fake\FakeInvokableClass', array('foo' => 'foo')));
        $lazyCallable = $this->container->lazyCallable([$this->container->lazyGet('invokable_class'), '__invoke']);
        $callableRunner = function(callable $callable) {
            return $callable('bar');
        };

        $this->assertInstanceOf('Aura\Di\Injection\LazyCallable', $lazyCallable);
        $actual = $callableRunner($lazyCallable);
        $expect = 'foobar';
        $this->assertSame($expect, $actual);
    }

    public function testLazyGetCall()
    {
        $this->container->set(
            'fake',
            $this->container->lazyNew('Aura\Di\Fake\FakeParentClass')
        );

        $lazy = $this->container->lazyGetCall('fake', 'mirror', 'foo');

        $this->assertInstanceOf('Aura\Di\Injection\Lazy', $lazy);
        $actual = $lazy();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testNewFactory()
    {
        $other = $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');

        $factory = $this->container->newFactory(
            'Aura\Di\Fake\FakeChildClass',
            [
                'foo' => 'foofoo',
                'zim' => $other,
            ],
            [
                'setFake' => 'fakefake',
            ]
        );

        $actual = $factory();

        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());
        $this->assertSame('fakefake', $actual->getFake());


        // create another one, should not be the same
        $extra = $factory();
        $this->assertNotSame($actual, $extra);
    }

    public function testNewInstance()
    {
        $this->assertFalse($this->container->isLocked());
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual);
        $this->assertTrue($this->container->isLocked());
    }

    public function testNewInstanceWithLazyParam()
    {
        $lazy = $this->container->lazy(function() {
            return new \Aura\Di\Fake\FakeOtherClass();
        });

        $class = 'Aura\Di\Fake\FakeParentClass';

        $actual = $this->container->newInstance($class, [
            'foo' => $lazy,
        ]);

        $this->assertInstanceOf($class, $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getFoo());
    }

    public function testNewInstanceWithSetter()
    {
        $this->container->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = 'fake_value';

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            'foo' => 'gir',
            'zim' => new \Aura\Di\Fake\FakeOtherClass(),
        ]);

        $this->assertSame('fake_value', $actual->getFake());
    }

    public function testNewInstanceWithMutation()
    {
        $this->container->mutations['Aura\Di\Fake\FakeInterfaceClass'][] = new FakeMutationClass('mutated');

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass');

        $this->assertSame('mutated', $actual->getFoo());
    }

    public function testNewInstanceWithLazyMutation()
    {
        $this->container->mutations['Aura\Di\Fake\FakeInterfaceClass'][] = $this->container->lazyNew(FakeMutationClass::class, [
            'fooValue' => 'mutated'
        ]);

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass');

        $this->assertSame('mutated', $actual->getFoo());
    }

    public function testNewInstanceWithLazyMutationInjectContainer()
    {
        $this->container->params['Aura\Di\Fake\FakeMutationWithDependencyClass']['container'] = $this->container;
        $this->container->mutations['Aura\Di\Fake\FakeInterfaceClass'][] = $this->container->lazyNew(FakeMutationWithDependencyClass::class);
        $this->container->set('service', $this->container->lazyNew(FakeOtherClass::class));

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass');

        $this->assertInstanceOf(FakeOtherClass::class, $actual->getFoo());
    }

    public function testHonorsSettersInterfacesAndOverrides()
    {
        $this->container->setters['Aura\Di\Fake\FakeInterface']['setFoo'] = 'initial';
        $this->container->setters['Aura\Di\Fake\FakeInterfaceClass2']['setFoo'] = 'override';

        // "inherits" initial value from interface
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass');
        $this->assertSame('initial', $actual->getFoo());

        // uses initial value "inherited" from parent
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass1');
        $this->assertSame('initial', $actual->getFoo());

        // overrides the initial "inherited" value
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass2');
        $this->assertSame('override', $actual->getFoo());

        // uses the "inherited" overridde value
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass3');
        $this->assertSame('override', $actual->getFoo());
    }

    public function testHonorsMutationInterfacesAndOverrides()
    {
        $this->container->mutations['Aura\Di\Fake\FakeInterfaceClass'][] = new FakeMutationClass('one');
        $this->container->mutations['Aura\Di\Fake\FakeInterfaceClass2'][] = new FakeMutationClass('two');

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass');
        $this->assertSame('one', $actual->getFoo());

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass1');
        $this->assertSame('one', $actual->getFoo());

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass2');
        $this->assertSame('two', $actual->getFoo());

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeInterfaceClass3');
        $this->assertSame('two', $actual->getFoo());
    }

    public function testNewInstanceWithLazySetter()
    {
        $lazy = $this->container->lazy(function() {
            return new \Aura\Di\Fake\FakeOtherClass();
        });

        $this->container->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = $lazy;

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            'foo' => 'gir',
            'zim' => new \Aura\Di\Fake\FakeOtherClass(),
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getFake());
    }

    public function testNewInstanceWithNonExistentSetter()
    {
        $this->container->setters['Aura\Di\Fake\FakeOtherClass']['setFakeNotExists'] = 'fake_value';

        $this->expectException('Aura\Di\Exception\SetterMethodNotFound');
        $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');
    }

    public function testNewInstanceWithPositionalParams()
    {
        $other = $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            'foofoo',
            $other,
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());

        // positional overrides names
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            0 => 'keepme',
            'foo' => 'bad',
            $other,
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getZim());
        $this->assertSame('keepme', $actual->getFoo());
    }

    public function testLazyValue()
    {
        $this->container->params['Aura\Di\Fake\FakeParentClass']['foo'] = $this->container->lazyValue('foo');
        $this->container->values['foo'] = 'bar';
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeParentClass');
        $this->assertSame('bar', $actual->getFoo());
    }

    public function testLazyValueWithLazies()
    {
        $this->container->params['Aura\Di\Fake\FakeParentClass']['foo'] = $this->container->lazyValue('foo');
        $this->container->values['foo'] = $this->container->lazyValue('bar');
        $this->container->values['bar'] = 'baz';
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeParentClass');
        $this->assertSame('baz', $actual->getFoo());
    }

    public function testResolveWithMissingParam()
    {
        $this->expectException('Aura\Di\Exception\MissingParam');
        $this->expectExceptionMessage('Aura\Di\Fake\FakeResolveClass::$fake');
        $this->container->newInstance('Aura\Di\Fake\FakeResolveClass');
    }

    public function testResolveWithoutMissingParam()
    {
        $this->container->params['Aura\Di\Fake\FakeResolveClass']['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeParentClass');
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $actual);
    }

    public function testDependencyLookupSimple() {
        $delegateContainer = new MinimalContainer([
            "foo" => function() {
                $obj = new \stdClass();
                $obj->foo = "bar";
                return $obj;
            }
        ]);

        $auraContainer = new Container(new InjectionFactory(new Resolver(new Reflector())), $delegateContainer);

        $lazy = $auraContainer->lazyGet('foo');

        $this->assertInstanceOf('Aura\Di\Injection\LazyGet', $lazy);

        $foo = $lazy();

        $this->assertInstanceOf('stdClass', $foo);
        $this->assertEquals('bar', $foo->foo);

        $actual = $auraContainer->getDelegateContainer();
        $this->assertSame($delegateContainer, $actual);
    }

    public function testDependencyLookup()
    {
        // A composite container with 2 containers: Aura and Picotainer.
        // 'service1' (in Aura) references
        // 'service2' (in Picotainer) that references
        // 'service3' (in Aura again)
        $compositeContainer = new CompositeContainer();
        $auraContainer = new Container(new InjectionFactory(new Resolver(new Reflector())), $compositeContainer);
        $auraContainer->params['Aura\Di\Fake\FakeParentClass']['foo'] = $auraContainer->lazyGet('service2');

        // Let's declare service 1
        $auraContainer->set('service1', $auraContainer->lazyNew('Aura\Di\Fake\FakeParentClass'));

        // Let's declare service 3
        $obj = new \stdClass();
        $obj->foo = "bar";
        $auraContainer->set('service3', $obj);

        $minimalContainer = new MinimalContainer(
            [
                // Let's declare service 2
                "service2" => function($container) {
                    return new FakeParamsClass([$container->get('service3')], null);
                },
            ],
            $compositeContainer
        );

        $compositeContainer->addContainer($minimalContainer);
        $compositeContainer->addContainer($auraContainer);

        $service1 = $compositeContainer->get('service1');

        $this->assertEquals('bar', $service1->getFoo()->array[0]->foo);
    }

    public function testContainerNamedParameterCanBeNull()
    {
        $container = new Container(new InjectionFactory(new Resolver(new Reflector())));

        $container->params['Aura\Di\Fake\FakeNullConstruct']['foo'] = null;
        $container->set('Foo', $container->lazyNew('Aura\Di\Fake\FakeNullConstruct'));

        try {
            $service = $container->get('Foo');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Was not able to set a null named parameter');
        }

        $this->assertInstanceOf('Aura\Di\Fake\FakeNullConstruct', $service);
    }

    public function testContainerNumberedParameterCanBeNull()
    {
        $container = new Container(new InjectionFactory(new Resolver(new Reflector())));

        $container->params['Aura\Di\Fake\FakeNullConstruct'][] = null;
        $container->set('Foo', $container->lazyNew('Aura\Di\Fake\FakeNullConstruct'));

        try {
            $service = $container->get('Foo');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Was not able to set a null numbered parameter');
        }

        $this->assertInstanceOf('Aura\Di\Fake\FakeNullConstruct', $service);
    }

    public function testContainerImplicitParentParametersCanBeNull()
    {
        $container = new Container(new InjectionFactory(new Resolver(new Reflector())));

        $container->params['Aura\Di\Fake\FakeNullConstruct'][] = null;
        $container->set('Foo', $container->lazyNew('Aura\Di\Fake\FakeNullConstruct'));
        $container->set('Bar', $container->lazyNew('Aura\Di\Fake\FakeChildNullConstruct'));

        try {
            $service = $container->get('Bar');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Was not able to set a null implicit named parameter');
        }

        $this->assertInstanceOf('Aura\Di\Fake\FakeNullConstruct', $service);
    }

    public function testContainerDefaultParamsForChildClass()
    {
        $container = new Container(new InjectionFactory(new Resolver(new Reflector())));

        $actual = $container->newInstance('Aura\Di\Fake\FakeClassWithDefaultParam');

        $this->assertSame(1, $actual->first);
        $this->assertSame(2, $actual->second);

        $actual = $container->newInstance('Aura\Di\Fake\FakeChildClassWithDefaultParam');
        $this->assertSame(1, $actual->first);
        $this->assertSame(3, $actual->second);
    }

    public function testContextualParams()
    {
        $this->container->params['Aura\Di\Fake\FakeClassNeedsContextA']['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeClassNeedsContextB');
        $this->container->params['Aura\Di\Fake\FakeClassNeedsContextB']['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeClassNeedsContextC');
        $this->container->params['Aura\Di\Fake\FakeClassNeedsContextC']['fake'] = '1';

        $lazy = $this->container->lazyNew('Aura\Di\Fake\FakeClassNeedsContextC');
        $this->assertSame('1', $lazy()->fake);

        $lazy = $this->container->lazyNew('Aura\Di\Fake\FakeClassNeedsContextA')
            ->withContext(new Blueprint('Aura\Di\Fake\FakeClassNeedsContextC', ['fake' => '2']));

        $this->assertSame('2', $lazy()->fake->fake->fake);

        $lazy = $this->container->lazyNew('Aura\Di\Fake\FakeClassNeedsContextC');
        $this->assertSame('1', $lazy()->fake);
    }
}
