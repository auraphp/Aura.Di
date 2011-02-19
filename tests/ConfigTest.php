<?php
namespace aura\di;

/**
 * Test class for Config.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    
    protected function setUp()
    {
        parent::setUp();
        $this->config = new Config;
    }
    
    public function testFetchReadsConstructorDefaults()
    {
        $expect = array('foo' => 'bar');
        list($actual_params, $actual_setter) = $this->config->fetch('aura\di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    /**
     * coverage for the "merged already" portion of the fetch() method
     */
    public function testFetchTwiceForMerge()
    {
        $expect = $this->config->fetch('aura\di\MockParentClass');
        $actual = $this->config->fetch('aura\di\MockParentClass');
        $this->assertSame($expect, $actual);
    }
    
    public function testFetchCapturesParentParams()
    {
        $expect = array(
            'foo' => 'bar',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->config->fetch('aura\di\MockChildClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testFetchCapturesExplicitParams()
    {
        $this->config = new Config;
        $params = $this->config->getParams();
        $params['aura\di\MockParentClass'] = array('foo' => 'zim');
        
        $expect = array('foo' => 'zim');
        list($actual_params, $actual_setter) = $this->config->fetch('aura\di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testFetchHonorsExplicitParentParams()
    {
        $this->config = new Config;
        $params = $this->config->getParams();
        $params['aura\di\MockParentClass'] = array('foo' => 'dib');
        
        $expect = array(
            'foo' => 'dib',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->config->fetch('aura\di\MockChildClass');
        $this->assertSame($expect, $actual_params);
        
        // for test coverage of the mock class
        $child = new \aura\di\MockChildClass('bar', new \aura\di\MockOtherClass);
    }
    
    public function testGetReflection()
    {
        $actual = $this->config->getReflect('aura\di\MockOtherClass');
        $this->assertType('ReflectionClass', $actual);
        $this->assertSame('aura\di\MockOtherClass', $actual->getName());
        $actual = $this->config->getReflect('aura\di\MockOtherClass');
    }
    
    public function testFetchCapturesParentSetter()
    {
        $setter = $this->config->getSetter();
        $setter['aura\di\MockParentClass']['setFake'] = 'fake1';
        
        list($actual_config, $actual_setter) = $this->config->fetch('aura\di\MockChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
        
    }
    
    public function testFetchCapturesOverrideSetter()
    {
        $setter = $this->config->getSetter();
        $setter['aura\di\MockParentClass']['setFake'] = 'fake1';
        $setter['aura\di\MockChildClass']['setFake'] = 'fake2';
        
        list($actual_config, $actual_setter) = $this->config->fetch('aura\di\MockChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }
}
