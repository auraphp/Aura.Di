<?php
namespace Aura\Di;

/**
 * Test class for Forge.
 */
class ForgeTest extends \PHPUnit_Framework_TestCase
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

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @todo Implement testGetConfig().
     */
    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->forge->getConfig());
    }

    /**
     * @todo Implement testNewInstance().
     */
    public function testNewInstance()
    {
        $actual = $this->forge->newInstance('Aura\Di\MockOtherClass');
        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual);
    }

    public function testNewInstanceWithLazyParam()
    {
        $lazy = new Lazy(function() {
            return new MockOtherClass;
        });

        $class = 'Aura\Di\MockParentClass';

        $actual = $this->forge->newInstance($class, array(
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

        $actual = $this->forge->newInstance('Aura\Di\MockChildClass', array(
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

        $actual = $this->forge->newInstance('Aura\Di\MockChildClass', array(
            'foo' => 'gir',
            'zim' => new MockOtherClass,
        ));

        $this->assertInstanceOf('Aura\Di\MockOtherClass', $actual->getFake());
    }

    public function testClone()
    {
        $clone = clone $this->forge;
        $this->assertNotSame($clone, $this->forge);
        $this->assertNotSame($clone->getConfig(), $this->forge->getConfig());
    }
}
