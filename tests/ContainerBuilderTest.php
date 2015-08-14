<?php
namespace Aura\Di;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;

    protected function setUp()
    {
        parent::setUp();
        $this->builder = new ContainerBuilder();
    }

    public function testNewConfiguredInstance()
    {
        $config_classes = [
            'Aura\Di\Fake\FakeLibraryConfig',
            'Aura\Di\Fake\FakeProjectConfig',
        ];

        $di = $this->builder->newConfiguredInstance($config_classes);

        $this->assertInstanceOf('Aura\Di\Container', $di);

        $expect = 'zim';
        $actual = $di->get('library_service');
        $this->assertSame($expect, $actual->foo);

        $expect = 'gir';
        $actual = $di->get('project_service');
        $this->assertSame($expect, $actual->baz);

        $this->assertTrue($di->hasConfigured('Aura\Di\Fake\FakeLibraryConfig'));
        $this->assertTrue($di->hasConfigured('Aura\Di\Fake\FakeProjectConfig'));
        $this->assertFalse($di->hasConfigured('foo'));
    }

    public function testSerializeAndUnserialize()
    {
        $di = $this->builder->newInstance();

        $di->params['Aura\Di\Fake\FakeParamsClass'] = [
            'array' => [],
            'empty' => 'abc'
        ];

        $instance = $di->newInstance('Aura\Di\Fake\FakeParamsClass');

        $this->assertInstanceOf('Aura\Di\Fake\FakeParamsClass', $instance);

        $serialized = serialize($di);
        $unserialized = unserialize($serialized);

        $instance = $unserialized->newInstance('Aura\Di\Fake\FakeParamsClass', [
            'array' => ['a' => 1]
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeParamsClass', $instance);
    }
}
