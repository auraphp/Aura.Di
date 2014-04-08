<?php
namespace Aura\Di;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    
    protected function setUp()
    {
        parent::setUp();
        $this->container = new Container(new Factory);
    }
    
    protected function tearDown()
    {
        parent::tearDown();
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
    
    public function testReadsConstructorDefaults()
    {
        $expect = array('foo' => 'bar');
        list($actual_params, $actual_setter) = $this->container->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    /**
     * coverage for the "merged already" portion of the fetch() method
     */
    public function testTwiceForMerge()
    {
        $expect = $this->container->getUnified('Aura\Di\FakeParentClass');
        $actual = $this->container->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual);
    }
    
    public function testHonorsParentParams()
    {
        $expect = array(
            'foo' => 'bar',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->container->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testHonorsExplicitParams()
    {
        $this->container->params['Aura\Di\FakeParentClass'] = array('foo' => 'zim');
        
        $expect = array('foo' => 'zim');
        list($actual_params, $actual_setter) = $this->container->getUnified('Aura\Di\FakeParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testHonorsExplicitParentParams()
    {
        $this->container->params['Aura\Di\FakeParentClass'] = array('foo' => 'dib');
        
        $expect = array(
            'foo' => 'dib',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->container->getUnified('Aura\Di\FakeChildClass');
        $this->assertSame($expect, $actual_params);
        
        // for test coverage of the mock class
        $child = new \Aura\Di\FakeChildClass('bar', new \Aura\Di\FakeOtherClass);
    }
    
    public function testHonorsParentSetter()
    {
        $this->container->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';
        
        list($actual_config, $actual_setter) = $this->container->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
        
    }
    
    public function testHonorsOverrideSetter()
    {
        $this->container->setter['Aura\Di\FakeParentClass']['setFake'] = 'fake1';
        $this->container->setter['Aura\Di\FakeChildClass']['setFake'] = 'fake2';
        
        list($actual_config, $actual_setter) = $this->container->getUnified('Aura\Di\FakeChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }
    
    public function testHonorsTraitSetter()
    {
        if (phpversion() < '5.4') {
            $this->markTestSkipped("No traits before PHP 5.4");
        }
        
        $this->container->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';
        
        list($actual_config, $actual_setter) = $this->container->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
        
    }

    public function testHonorsOverrideTraitSetter()
    {
        if (phpversion() < '5.4') {
            $this->markTestSkipped("No traits before PHP 5.4");
        }
        
        $this->container->setter['Aura\Di\FakeTrait']['setFake'] = 'fake1';
        $this->container->setter['Aura\Di\FakeClassWithTrait']['setFake'] = 'fake2';
        
        list($actual_config, $actual_setter) = $this->container->getUnified('Aura\Di\FakeClassWithTrait');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }
    
    public function testReflectionFailure()
    {
        $this->setExpectedException('Aura\Di\Exception\ReflectionFailure');
        $this->container->newInstance('NoSuchClass');
    }
    
    public function testHonorsLazyParams()
    {
        $this->container->params['Aura\Di\FakeParentClass']['foo'] = $this->container->lazyNew('Aura\Di\FakeOtherClass');
        $object = $this->container->newInstance('Aura\Di\FakeParentClass');
        $actual = $object->getFoo();
        $this->assertInstanceOf('Aura\Di\FakeOtherClass', $actual);
    }
}
