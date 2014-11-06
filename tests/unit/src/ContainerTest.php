<?php
namespace Aura\Di;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new Factory;
        $this->container = new Container($this->factory);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testMagicGet()
    {
        $this->container->params['foo'] = 'bar';
        $this->container->setter['baz'] = 'dib';
        $this->container->setters['zim'] = 'gir';

        $expect = array('foo' => 'bar');
        $this->assertSame($expect, $this->container->params);

        $expect = array('baz' => 'dib', 'zim' => 'gir');
        $this->assertSame($expect, $this->container->setter);
        $this->assertSame($expect, $this->container->setters);
    }

    public function testHasGet()
    {
        $expect = new \StdClass;
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
            return new \Aura\Di\FakeParentClass;
        });

        $actual = $this->container->get('foo');
        $this->assertInstanceOf('Aura\Di\FakeParentClass', $actual);
    }

    public function testGetServicesAndInstances()
    {
        $this->container->set('foo', new \StdClass);
        $this->container->set('bar', new \StdClass);
        $this->container->set('baz', new \StdClass);

        $expect = array('foo', 'bar', 'baz');
        $actual = $this->container->getServices();
        $this->assertSame($expect, $actual);

        $service = $this->container->get('bar');
        $expect = array('bar');
        $actual = $this->container->getInstances();
        $this->assertSame($expect, $actual);
    }

    public function testLazyGet()
    {
        $this->container->set('foo', function() {
            return new FakeOtherClass;
        });

        $lazy = $this->container->lazyGet('foo');

        $this->assertInstanceOf('Aura\Di\LazyGet', $lazy);

        $foo = $lazy();

        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $foo);
    }

    public function testMagicGetNoSuchProperty()
    {
        $this->setExpectedException('UnexpectedValueException');
        $actual = $this->container->no_such_property;
    }

    public function testNewInstanceWithDefaults()
    {
        $instance = $this->container->newInstance('Aura\Di\FakeParentClass');
        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testNewInstanceWithOverride()
    {
        $instance = $this->container->newInstance(
            'Aura\Di\FakeParentClass',
            array('foo' => 'dib')
        );

        $expect = 'dib';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testLazyNew()
    {
        $lazy = $this->container->lazyNew('Aura\Di\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\LazyNew', $lazy);
        $foo = $lazy();
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $foo);
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
        $this->container->set('foo', function() { return new StdClass; });
    }

    public function testLazyInclude()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyInclude($file);
        $this->assertInstanceOf('Aura\Di\LazyInclude', $lazy);
        $actual = $lazy();
        $expect = array('foo' => 'bar');
        $this->assertSame($expect, $actual);
    }

    public function testLazyRequire()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyRequire($file);
        $this->assertInstanceOf('Aura\Di\LazyRequire', $lazy);
        $actual = $lazy();
        $expect = array('foo' => 'bar');
        $this->assertSame($expect, $actual);
    }

    public function testLazy()
    {
        $lazy = $this->container->lazy(
            array($this->container->lazyNew('Aura\Di\FakeParentClass'), 'mirror'),
            $this->container->lazy(function () { return 'mirror'; })
        );

        $this->assertInstanceOf('Aura\Di\Lazy', $lazy);
        $actual = $lazy();
        $expect = 'mirror';
        $this->assertSame($expect, $actual);
    }

    public function testNewFactory()
    {
        $other = $this->container->newInstance('Aura\Di\FakeOtherClass');

        $factory = $this->container->newFactory(
            'Aura\Di\FakeChildClass',
            array(
                'foo' => 'foofoo',
                'zim' => $other,
            ),
            array(
                'setFake' => 'fakefake',
            )
        );

        $actual = $factory();

        $this->assertInstanceOf('Aura\Di\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());
        $this->assertSame('fakefake', $actual->getFake());


        // create another one, should not be the same
        $extra = $factory();
        $this->assertNotSame($actual, $extra);
    }

    public function testNewInstance()
    {
        $actual = $this->container->newInstance('Aura\Di\FakeOtherClass');
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual);
    }

    public function testNewInstanceWithLazyParam()
    {
        $lazy = $this->container->lazy(function() {
            return new FakeOtherClass;
        });

        $class = 'Aura\Di\FakeParentClass';

        $actual = $this->container->newInstance($class, array(
            'foo' => $lazy,
        ));

        $this->assertInstanceOf($class, $actual);
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->getFoo());
    }

    public function testNewInstanceWithSetter()
    {
        $class = 'Aura\Di\FakeChildClass';
        $this->container->setter['Aura\Di\FakeChildClass']['setFake'] = 'fake_value';

        $actual = $this->container->newInstance('Aura\Di\FakeChildClass', array(
            'foo' => 'gir',
            'zim' => new FakeOtherClass,
        ));

        $this->assertSame('fake_value', $actual->getFake());
    }

    public function testHonorsInterfacesAndOverrides()
    {
        $this->container->setter['Aura\Di\FakeInterface']['setFoo'] = 'initial';
        $this->container->setter['Aura\Di\FakeInterfaceClass2']['setFoo'] = 'override';

        // "inherits" initial value from interface
        $actual = $this->container->newInstance('Aura\Di\FakeInterfaceClass');
        $this->assertSame('initial', $actual->getFoo());

        // uses initial value "inherited" from parent
        $actual = $this->container->newInstance('Aura\Di\FakeInterfaceClass1');
        $this->assertSame('initial', $actual->getFoo());

        // overrides the initial "inherited" value
        $actual = $this->container->newInstance('Aura\Di\FakeInterfaceClass2');
        $this->assertSame('override', $actual->getFoo());

        // uses the "inherited" overridde value
        $actual = $this->container->newInstance('Aura\Di\FakeInterfaceClass3');
        $this->assertSame('override', $actual->getFoo());
    }

    public function testnewInstanceWithLazySetter()
    {
        $lazy = $this->container->lazy(function() {
            return new FakeOtherClass;
        });

        $class = 'Aura\Di\FakeChildClass';
        $this->container->setter['Aura\Di\FakeChildClass']['setFake'] = $lazy;

        $actual = $this->container->newInstance('Aura\Di\FakeChildClass', array(
            'foo' => 'gir',
            'zim' => new FakeOtherClass,
        ));

        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->getFake());
    }

    public function testNewInstanceWithNonExistentSetter()
    {
        $class = 'Aura\Di\FakeOtherClass';
        $this->container->setter['Aura\Di\FakeOtherClass']['setFakeNotExists'] = 'fake_value';
        $this->setExpectedException('Aura\Di\Exception\SetterMethodNotFound');
        $actual = $this->container->newInstance('Aura\Di\FakeOtherClass');
    }

    public function testNewInstanceWithPositionalParams()
    {
        $other = $this->container->newInstance('Aura\Di\FakeOtherClass');

        $actual = $this->container->newInstance('Aura\Di\FakeChildClass', array(
            'foofoo',
            $other,
        ));

        $this->assertInstanceOf('Aura\Di\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());

        // positional overrides names
        $actual = $this->container->newInstance('Aura\Di\FakeChildClass', array(
            0 => 'keepme',
            'foo' => 'bad',
            $other,
        ));

        $this->assertInstanceOf('Aura\Di\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual->getZim());
        $this->assertSame('keepme', $actual->getFoo());
    }

    public function testLazyValue()
    {
        $this->container->params['Aura\Di\FakeParentClass']['foo'] = $this->container->lazyValue('foo');
        $this->container->values['foo'] = 'bar';
        $actual = $this->container->newInstance('Aura\Di\FakeParentClass');
        $this->assertSame('bar', $actual->getFoo());
    }

    public function testResolve()
    {
        $this->container->types['Aura\Di\FakeParentClass'] = $this->container->lazyNew('Aura\Di\FakeChildClass');
        $actual = $this->container->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeResolveClass', $actual);
        $this->assertInstanceOf('Aura\Di\FakeChildClass', $actual->fake);
    }

    public function testSetAutoResolve()
    {
        $this->assertTrue($this->factory->auto_resolve);
        $this->container->setAutoResolve(false);
        $this->assertFalse($this->factory->auto_resolve);

        $this->container->lock();
        $this->setExpectedException('Aura\Di\Exception\ContainerLocked');
        $this->container->setAutoResolve(true);
    }

    public function testDisableAutoResolveWithMissingParam()
    {
        $this->container->setAutoResolve(false);
        $this->setExpectedException(
            'Aura\Di\Exception\MissingParam',
            'Aura\Di\FakeResolveClass::$fake'
        );
        $this->container->newInstance('Aura\Di\FakeResolveClass');
    }

    public function testDisableAutoResolveWithoutMissingParam()
    {
        $this->container->setAutoResolve(false);
        $this->container->params['Aura\Di\FakeResolveClass']['fake'] = $this->container->lazyNew('Aura\Di\FakeParentClass');
        $actual = $this->container->newInstance('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeResolveClass', $actual);
    }
}
