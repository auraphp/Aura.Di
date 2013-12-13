<?php
namespace Aura\Di;

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
        list($actual_params, $actual_setter) = $this->config->fetch('Aura\Di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    /**
     * coverage for the "merged already" portion of the fetch() method
     */
    public function testFetchTwiceForMerge()
    {
        $expect = $this->config->fetch('Aura\Di\MockParentClass');
        $actual = $this->config->fetch('Aura\Di\MockParentClass');
        $this->assertSame($expect, $actual);
    }
    
    public function testFetchCapturesParentParams()
    {
        $expect = array(
            'foo' => 'bar',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->config->fetch('Aura\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testFetchCapturesExplicitParams()
    {
        $this->config = new Config;
        $params = $this->config->getParams();
        $params['Aura\Di\MockParentClass'] = array('foo' => 'zim');
        
        $expect = array('foo' => 'zim');
        list($actual_params, $actual_setter) = $this->config->fetch('Aura\Di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }
    
    public function testFetchHonorsExplicitParentParams()
    {
        $this->config = new Config;
        $params = $this->config->getParams();
        $params['Aura\Di\MockParentClass'] = array('foo' => 'dib');
        
        $expect = array(
            'foo' => 'dib',
            'zim' => null,
        );
        
        list($actual_params, $actual_setter) = $this->config->fetch('Aura\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);
        
        // for test coverage of the mock class
        $child = new \Aura\Di\MockChildClass('bar', new \Aura\Di\MockOtherClass);
    }
    
    public function testGetReflection()
    {
        $actual = $this->config->getReflect('Aura\Di\MockOtherClass');
        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertSame('Aura\Di\MockOtherClass', $actual->getName());
        $actual = $this->config->getReflect('Aura\Di\MockOtherClass');
    }
    
    public function testFetchCapturesParentSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockParentClass']['setFake'] = 'fake1';
        
        list($actual_config, $actual_setter) = $this->config->fetch('Aura\Di\MockChildClass');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
        
    }
    
    public function testFetchCapturesOverrideSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockParentClass']['setFake'] = 'fake1';
        $setter['Aura\Di\MockChildClass']['setFake'] = 'fake2';
        
        list($actual_config, $actual_setter) = $this->config->fetch('Aura\Di\MockChildClass');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
    }
    
    public function testFetchCapturesTraitSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockTrait']['setFake'] = 'fake1';
        
        list($actual_config, $actual_setter) = $this->config->fetch('Aura\Di\MockClassWithTrait');
        $expect = array('setFake' => 'fake1');
        $this->assertSame($expect, $actual_setter);
        
    }

    public function testFetchCapturesOverrideTraitSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Aura\Di\MockTrait']['setFake'] = 'fake1';
        $setter['Aura\Di\MockClassWithTrait']['setFake'] = 'fake2';
        
        list($actual_config, $actual_setter) = $this->config->fetch('Aura\Di\MockClassWithTrait');
        $expect = array('setFake' => 'fake2');
        $this->assertSame($expect, $actual_setter);
        
    }
    
    /**
     * @expectedException        Aura\Di\Exception\ReflectionFailure
     */
    public function testExceptionOnGetReflect()
    {
        $this->config->getReflect('NoSuchClass');
    }
}
