<?php
namespace Aura\Di;

/**
 * Test class for Forge.
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Forge
     */
    protected $forge;
    
    protected $config;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->config = new Config;
        $this->forge = new Forge($this->config);
    }
    
    protected function newFactory(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new Factory($this->forge, $class, $params, $setter);
    }
    
    public function test__invoke()
    {
        $other = $this->forge->newInstance('Aura\Di\MockOtherClass');
        
        $factory = $this->newFactory(
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
}
