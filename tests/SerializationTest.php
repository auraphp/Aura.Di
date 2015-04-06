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
        $builder = new ContainerBuilder();
        $this->container = $builder->newInstance();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testSerializeAndUnserializeOfReflection()
    {
        $this->container->params['Aura\Di\FakeParamsClass'] = [
            'array' => [],
            'empty' => 'abc'
        ];

        $instance = $this->container->newInstance('Aura\Di\FakeParamsClass');

        $this->assertInstanceOf('Aura\Di\FakeParamsClass', $instance);

        $this->container = serialize($this->container);
        $this->container = unserialize($this->container);

        $instance = $this->container->newInstance('Aura\Di\FakeParamsClass', [
            'array' => ['a' => 1]
        ]);

        $this->assertInstanceOf('Aura\Di\FakeParamsClass', $instance);
    }
}
