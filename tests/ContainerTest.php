<?php
namespace aura\di;

/**
 * Test class for Dependency.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;
    
    protected $config;
    
    protected $forge;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->config  = new Config;
        $this->forge   = new Forge($this->config);
        $this->container = new Container($this->forge);
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * @todo Implement testHas().
     */
    public function testHasGet()
    {
        $expect = new \StdClass;
        $this->container->set('foo', $expect);
        
        $this->assertTrue($this->container->has('foo'));
        $this->assertFalse($this->container->has('bar'));
        
        $actual = $this->container->get('foo');
        $this->assertSame($expect, $actual);
    }
    
    /**
     * @expectedException aura\di\Exception_ServiceInvalid
     */
    public function testInitInvalidService()
    {
        $this->container->set('foo', 'bar');
    }
    
    /**
     * @expectedException aura\di\Exception_ServiceNotFound
     */
    public function testGetNoSuchService()
    {
        $this->container->get('foo');
    }
    
    public function testGetServiceInsideClosure()
    {
        $di = $this->container;
        $di->set('foo', function() use ($di) {
            return new \aura\di\MockParentClass;
        });
        
        $actual = $this->container->get('foo');
        $this->assertType('aura\di\MockParentClass', $actual);
    }
    
    /**
     * @todo Implement testGetServices().
     */
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
        
        $this->assertType('aura\di\Lazy', $lazy);
        
        $foo = $lazy();
        
        $this->assertType('aura\di\MockOtherClass', $foo);
    }
    
    public function testMagicGet()
    {
        $this->assertSame($this->container->params, $this->config->getParams());
        $this->assertSame($this->container->setter, $this->config->getSetter());
    }
    
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testMagicGetNoSuchProperty()
    {
        $actual = $this->container->no_such_property;
    }
    
    /**
     * @todo Implement testNewInstance().
     */
    public function testNewInstanceWithDefaults()
    {
        $instance = $this->container->newInstance('aura\di\MockParentClass');
        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }
    
    public function testNewInstanceWithOverride()
    {
        $instance = $this->container->newInstance(
            'aura\di\MockParentClass',
            array(
                'foo' => 'dib'
            )
        );
        
        $expect = 'dib';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }
    
    public function testLazyNew()
    {
        $lazy = $this->container->lazyNew('aura\di\MockOtherClass');
        $this->assertType('aura\di\Lazy', $lazy);
        $foo = $lazy();
        $this->assertType('aura\di\MockOtherClass', $foo);
    }
    
    public function testClone()
    {
        $clone = clone $this->container;
        $this->assertNotSame($clone->getForge(), $this->container->getForge());
    }
    
    /**
     * @expectedException aura\di\Exception_ContainerLocked
     */
    public function testLockedConfig()
    {
        $this->container->lock();
        $params = $this->container->params;
    }
    
    /**
     * @expectedException aura\di\Exception_ContainerLocked
     */
    public function testLockedSet()
    {
        $this->container->lock();
        $this->container->set('foo', function() { return new StdClass; });
    }
}
