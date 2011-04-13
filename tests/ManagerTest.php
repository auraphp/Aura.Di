<?php
namespace aura\di;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    
    protected $forge;
    
    protected $manager;
    
    protected function setUp()
    {
        parent::setUp();
        $this->config  = new Config;
        $this->forge   = new Forge($this->config);
        $this->manager = new Manager($this->forge);
    }
    
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    public function testNewAndGetContainer()
    {
        $this->manager->params['aura\di\MockParentClass']['foo'] = 'dib';
        
        $mock = $this->manager->newContainer('mock');
        $mock->params['aura\di\MockParentClass']['foo'] = 'zim';
        
        // make sure two containers give different objects
        $a = $this->manager->newInstance('aura\di\MockParentClass');
        $b = $mock->newInstance('aura\di\MockParentClass');
        
        $this->assertNotSame($a->getFoo(), $b->getFoo());
        
        // can we get the container?
        $actual = $this->manager->getContainer('mock');
        $this->assertSame($mock, $actual);
    }
    
    /**
     * @expectedException aura\di\Exception_ContainerExists
     */
    public function testNewContainerExists()
    {
        $mock = $this->manager->newContainer('mock');
        $mock = $this->manager->newContainer('mock');
    }
    
    /**
     * @expectedException aura\di\Exception_ContainerNotFound
     */
    public function testGetContainerNotFound()
    {
        $mock = $this->manager->getContainer('mock');
    }
    
    public function testLock()
    {
        $mock = $this->manager->newContainer('mock');
        $this->manager->lock();
        $this->assertTrue($this->manager->isLocked());
        foreach ($this->manager->getContainers() as $name) {
            $container = $this->manager->getContainer($name);
            $this->assertTrue($container->isLocked());
        }
    }
    
    public function testCloneContainer()
    {
        $mock = $this->manager->newContainer('mock');
        $mock->set('parent', function() use ($mock) {
            return $mock->newInstance('aura\di\MockParentClass');
        });
        
        $clone = $this->manager->cloneContainer('mock');
        
        $this->assertNotSame($mock, $clone);
        $this->assertNotSame($mock->get('parent'), $clone->get('parent'));
    }
    
    public function testLazyCloneContainer()
    {
        $mock = $this->manager->newContainer('mock');
        $mock->set('parent', function() use ($mock) {
            return $mock->newInstance('aura\di\MockParentClass');
        });
        
        $lazy = $this->manager->lazyCloneContainer('mock');
        $this->assertType('aura\di\Lazy', $lazy);
        $mock = $lazy();
        $this->assertType('aura\di\Container', $mock);
    }
}
