<?php
namespace Aura\Di;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    protected function setUp()
    {
        parent::setUp();
        $builder = new ContainerBuilder;
        $this->container = $builder->newInstance();
    }

    protected function tearDown()
    {
        parent::tearDown();
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

    public function testInitInvalidService()
    {
        $this->setExpectedException('Aura\Di\Exception\ServiceNotObject');
        $this->container->set('foo', 'bar');
    }

    public function testGetNoSuchService()
    {
        $this->setExpectedException('Aura\Di\Exception\ServiceNotFound');
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

        $this->assertInstanceOf('Aura\Di\LazyGet', $lazy);

        $foo = $lazy();

        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $foo);
    }

    public function testMagicGetNoSuchProperty()
    {
        $this->setExpectedException('UnexpectedValueException');
        $actual = $this->container->no_such_property;
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

    public function testLazyNew()
    {
        $lazy = $this->container->lazyNew('Aura\Di\Fake\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\LazyNew', $lazy);
        $foo = $lazy();
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $foo);
    }

    public function testLockedConfig()
    {
        $this->container->lock();
        $this->setExpectedException('Aura\Di\Exception\ContainerLocked');
        $params = $this->container->params;
    }

    public function testLockedSet()
    {
        $this->container->lock();
        $this->setExpectedException('Aura\Di\Exception\ContainerLocked');
        $this->container->set('foo', function() { return (object) []; });
    }

    public function testLazyInclude()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyInclude($file);
        $this->assertInstanceOf('Aura\Di\LazyInclude', $lazy);
        $actual = $lazy();
        $expect = ['foo' => 'bar'];
        $this->assertSame($expect, $actual);
    }

    public function testLazyRequire()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyRequire($file);
        $this->assertInstanceOf('Aura\Di\LazyRequire', $lazy);
        $actual = $lazy();
        $expect = ['foo' => 'bar'];
        $this->assertSame($expect, $actual);
    }

    public function testLazy()
    {
        $lazy = $this->container->lazy(
            [$this->container->lazyNew('Aura\Di\Fake\FakeParentClass'), 'mirror'],
            $this->container->lazy(function () { return 'mirror'; })
        );

        $this->assertInstanceOf('Aura\Di\Lazy', $lazy);
        $actual = $lazy();
        $expect = 'mirror';
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
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual);
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
        $class = 'Aura\Di\Fake\FakeChildClass';
        $this->container->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = 'fake_value';

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            'foo' => 'gir',
            'zim' => new \Aura\Di\Fake\FakeOtherClass(),
        ]);

        $this->assertSame('fake_value', $actual->getFake());
    }

    public function testHonorsInterfacesAndOverrides()
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

    public function testnewInstanceWithLazySetter()
    {
        $lazy = $this->container->lazy(function() {
            return new \Aura\Di\Fake\FakeOtherClass();
        });

        $class = 'Aura\Di\Fake\FakeChildClass';
        $this->container->setters['Aura\Di\Fake\FakeChildClass']['setFake'] = $lazy;

        $actual = $this->container->newInstance('Aura\Di\Fake\FakeChildClass', [
            'foo' => 'gir',
            'zim' => new \Aura\Di\Fake\FakeOtherClass(),
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getFake());
    }

    public function testNewInstanceWithNonExistentSetter()
    {
        $class = 'Aura\Di\Fake\FakeOtherClass';
        $this->container->setters['Aura\Di\Fake\FakeOtherClass']['setFakeNotExists'] = 'fake_value';
        $this->setExpectedException('Aura\Di\Exception\SetterMethodNotFound');
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeOtherClass');
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

    public function testResolveWithMissingParam()
    {
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\Fake\FakeResolveClass::$fake'
        );
        $this->container->newInstance('Aura\Di\Fake\FakeResolveClass');
    }

    public function testResolveWithoutMissingParam()
    {
        $this->container->params['Aura\Di\Fake\FakeResolveClass']['fake'] = $this->container->lazyNew('Aura\Di\Fake\FakeParentClass');
        $actual = $this->container->newInstance('Aura\Di\Fake\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $actual);
    }
}
