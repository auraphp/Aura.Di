<?php
namespace aura\di;

/**
 * Test class for Dependency.
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;
    
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
        $this->manager = new Manager($this->forge);
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    public function testMagicGet()
    {
        $this->assertSame($this->manager->params, $this->config->getParams());
        $this->assertSame($this->manager->setter, $this->config->getSetter());
    }
    
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testMagicGetNoSuchProperty()
    {
        $actual = $this->manager->no_such_property;
    }
    
    /**
     * @todo Implement testNewInstance().
     */
    public function testNewInstanceWithDefaults()
    {
        $instance = $this->manager->newInstance('aura\di\MockParentClass');
        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }
    
    public function testNewInstanceWithOverride()
    {
        $instance = $this->manager->newInstance(
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
        $lazy = $this->manager->lazyNew('aura\di\MockOtherClass');
        $this->assertType('aura\di\Lazy', $lazy);
        $foo = $lazy();
        $this->assertType('aura\di\MockOtherClass', $foo);
    }
    
    public function testNewContainer()
    {
        $container = $this->manager->newContainer();
        $this->assertType('aura\di\Container', $container);
    }
}
