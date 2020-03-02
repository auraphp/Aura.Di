<?php
namespace Aura\Di;

use Aura\Di\Fake\FakeParentClass;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ContainerBuilder();
    }

    public function testAutoResolverInstance()
    {
        $builder = new ContainerBuilder();
        $container = $builder->newInstance(ContainerBuilder::AUTO_RESOLVE);
        $resolver = $container->getInjectionFactory()->getResolver();
        $this->assertInstanceOf('Aura\Di\Resolver\AutoResolver', $resolver);
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
    }

    public function testNewConfiguredInstanceViaObject()
    {
        $config_classes = [
            new \Aura\Di\Fake\FakeLibraryConfig,
            new \Aura\Di\Fake\FakeProjectConfig,
        ];

        $di = $this->builder->newConfiguredInstance($config_classes);

        $this->assertInstanceOf('Aura\Di\Container', $di);

        $expect = 'zim';
        $actual = $di->get('library_service');
        $this->assertSame($expect, $actual->foo);

        $expect = 'gir';
        $actual = $di->get('project_service');
        $this->assertSame($expect, $actual->baz);
    }

    public function testInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $this->builder->newConfiguredInstance([[]]);
    }

    public function testInvalidDuckType()
    {
        $this->expectException('InvalidArgumentException');
        $this->builder->newConfiguredInstance([(object) []]);
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

    public function testSerializeAndUnserializeLazy()
    {
        $di = $this->builder->newInstance();

        $di->params['Aura\Di\Fake\FakeResolveClass'] = [
            'fake' => $di->lazyNew(FakeParentClass::class),
        ];

        $instance = $di->newInstance('Aura\Di\Fake\FakeResolveClass');

        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $instance);
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $instance->fake);

        $serialized = serialize($di);
        $unserialized = unserialize($serialized);

        $instance = $unserialized->newInstance('Aura\Di\Fake\FakeResolveClass');

        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $instance);
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $instance->fake);
    }

    public function testSerializeAndUnserializeServices()
    {
        $di = $this->builder->newInstance();

        $di->params['Aura\Di\Fake\FakeResolveClass'] = [
            'fake' => $di->lazyNew(FakeParentClass::class),
        ];

        $di->set('fake', $di->lazyNew('Aura\Di\Fake\FakeResolveClass'));

        $instance = $di->get('fake');

        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $instance);
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $instance->fake);

        $serialized = serialize($di);
        $unserialized = unserialize($serialized);

        $instance = $unserialized->get('fake');

        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $instance);
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $instance->fake);
    }

    public function testSerializeAndUnserializeLazyCallable()
    {
        $di = $this->builder->newInstance();

        $di->params['Aura\Di\Fake\FakeResolveClass'] = [
            'fake' => $di->lazy(function () {
                return new FakeParentClass();
            }),
        ];

        $instance = $di->newInstance('Aura\Di\Fake\FakeResolveClass');

        $this->assertInstanceOf('Aura\Di\Fake\FakeResolveClass', $instance);
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $instance->fake);

        $this->expectException(\Exception::class);
        serialize($di);
    }
}
