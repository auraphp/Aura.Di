<?php
namespace Aura\Di;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    
    protected $config;
    
    protected function setUp()
    {
        parent::setUp();
        $this->config  = new Config;
        $this->container = new Container($this->config);
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
            return new \Aura\Di\MockParentClass;
        });
        
        $actual = $this->container->get('foo');
        $this->assertInstanceOf('Aura\Di\MockParentClass', $actual);
    }
    
    public function testGetDefsAndServices()
    {
        $this->container->set('foo', new \StdClass);
        $this->container->set('bar', new \StdClass);
        $this->container->set('baz', new \StdClass);
        
        $expect = array('foo', 'bar', 'baz');
        $actual = $this->container->getDefs();
        $this->assertSame($expect, $actual);
        
        $service = $this->container->get('bar');
        $expect = array('bar');
        $actual = $this->container->getServices();
        $this->assertSame($expect, $actual);
    }
    
    public function testLazyGet()
    {
        $this->container->set('foo', function() {
            return new MockOtherClass;
        });
        
        $lazy = $this->container->lazyGet('foo');
        
        $this->assertInstanceOf('Aura\Di\LazyInterface', $lazy);
        
        $foo = $lazy();
        
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $foo);
    }
    
    public function testMagicGet()
    {
        $this->assertSame($this->container->params, $this->config->getParams());
        $this->assertSame($this->container->setter, $this->config->getSetter());
    }
    
    public function testMagicGetNoSuchProperty()
    {
        $this->setExpectedException('UnexpectedValueException');
        $actual = $this->container->no_such_property;
    }
    
    public function testNewInstanceWithDefaults()
    {
        $instance = $this->container->newInstance('Aura\Di\MockParentClass');
        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }
    
    public function testNewInstanceWithOverride()
    {
        $instance = $this->container->newInstance(
            'Aura\Di\MockParentClass',
            array('foo' => 'dib')
        );
        
        $expect = 'dib';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }
    
    public function testLazyNew()
    {
        $lazy = $this->container->lazyNew('Aura\Di\MockOtherClass');
        $this->assertInstanceOf('Aura\Di\LazyInterface', $lazy);
        $foo = $lazy();
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $foo);
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
        $this->assertInstanceOf('Aura\Di\LazyInterface', $lazy);
        $actual = $lazy();
        $expect = array('foo' => 'bar');
        $this->assertSame($expect, $actual);
    }
    
    public function testLazyRequire()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazy = $this->container->lazyRequire($file);
        $this->assertInstanceOf('Aura\Di\LazyInterface', $lazy);
        $actual = $lazy();
        $expect = array('foo' => 'bar');
        $this->assertSame($expect, $actual);
    }
    
    public function testLazy()
    {
        $lazy = $this->container->lazy(
            array($this->container->lazyNew('Aura\Di\MockParentClass'), 'mirror'),
            $this->container->lazy(function () { return 'mirror'; })
        );
        
        $this->assertInstanceOf('Aura\Di\LazyInterface', $lazy);
        $actual = $lazy();
        $expect = 'mirror';
        $this->assertSame($expect, $actual);
    }
    
    public function testNewFactory()
    {
        $other = $this->container->newInstance('Aura\Di\MockOtherClass');
        
        $factory = $this->container->newFactory(
            'Aura\Di\MockChildClass',
            array(
                'foo' => 'foofoo',
                'zim' => $other,
            ),
            array(
                'setFake' => 'fakefake',
            )
        );
        
        $actual = $factory();
        
        $this->assertInstanceOf('Aura\Di\MockChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());
        $this->assertSame('fakefake', $actual->getFake());
        
        
        // create another one, should not be the same
        $extra = $factory();
        $this->assertNotSame($actual, $extra);
    }
    
    public function testNewInstance()
    {
        $actual = $this->container->newInstance('Aura\Di\MockOtherClass');
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual);
    }
    
    public function testNewInstanceWithLazyParam()
    {
        $lazy = new Lazy(function() {
            return new MockOtherClass;
        });
        
        $class = 'Aura\Di\MockParentClass';
        
        $actual = $this->container->newInstance($class, array(
            'foo' => $lazy,
        ));
        
        $this->assertInstanceOf($class, $actual);
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getFoo());
    }
    
    public function testNewInstanceWithSetter()
    {
        $class = 'Aura\Di\MockChildClass';
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockChildClass']['setFake'] = 'fake_value';
        
        $actual = $this->container->newInstance('Aura\Di\MockChildClass', array(
            'foo' => 'gir',
            'zim' => new MockOtherClass,
        ));
        
        $this->assertSame('fake_value', $actual->getFake());
    }
    
    public function testnewInstanceWithLazySetter()
    {
        $lazy = new Lazy(function() {
            return new MockOtherClass;
        });
        
        $class = 'Aura\Di\MockChildClass';
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockChildClass']['setFake'] = $lazy;
        
        $actual = $this->container->newInstance('Aura\Di\MockChildClass', array(
            'foo' => 'gir',
            'zim' => new MockOtherClass,
        ));
        
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getFake());
    }
    
    public function testNewInstanceWithNonExistentSetter()
    {
        $class = 'Aura\Di\MockOtherClass';
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockOtherClass']['setFakeNotExists'] = 'fake_value';
        $this->setExpectedException('Aura\Di\Exception\SetterMethodNotFound');
        $actual = $this->container->newInstance('Aura\Di\MockOtherClass');
    }
    
    public function testNewInstanceWithPositionalParams()
    {
        $other = $this->container->newInstance('Aura\Di\MockOtherClass');
        
        $actual = $this->container->newInstance('Aura\Di\MockChildClass', array(
            'foofoo',
            $other,
        ));
        
        $this->assertInstanceOf('Aura\Di\MockChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());
        
        // positional overrides names
        $actual = $this->container->newInstance('Aura\Di\MockChildClass', array(
            0 => 'keepme',
            'foo' => 'bad',
            $other,
        ));
        
        $this->assertInstanceOf('Aura\Di\MockChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getZim());
        $this->assertSame('keepme', $actual->getFoo());
    }
}
