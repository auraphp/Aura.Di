<?php
namespace Aura\Di;

class SerializationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aura\Di\Container
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = new Container(new Factory(new Resolver(new Reflector())));
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testSerializeAndUnserializeOfReflection()
    {
        $this->container->setAutoResolve(false);

        $this->container->params['Aura\Di\FakeParamsClass'] = array(
            'array' => array(),
            'empty' => 'abc'
        );

        $instance = $this->container->newInstance('Aura\Di\FakeParamsClass');

        $this->assertInstanceOf('Aura\Di\FakeParamsClass', $instance);

        $this->container = serialize($this->container);
        $this->container = unserialize($this->container);

        $instance = $this->container->newInstance('Aura\Di\FakeParamsClass', array('array' => array('a' => 1)));

        $this->assertInstanceOf('Aura\Di\FakeParamsClass', $instance);
    }
}
