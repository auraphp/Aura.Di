<?php
namespace Aura\Di\Resolver;

use Aura\Di\Container;
use Aura\Di\Fake\FakeInterfaceClass;
use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Injection\LazyNew;

class AutoResolverTest extends ResolverTest
{
    /**
     * @var AutoResolver
     */
    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new AutoResolver(new Reflector());
    }

    public function testMissingParam()
    {
        $actual = $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeResolveClass'));
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $actual->fake);
    }

    public function testAutoResolveExplicit()
    {
        $this->resolver->types['Aura\Di\Fake\FakeParentClass'] = new LazyNew(
            $this->resolver,
            new Blueprint('Aura\Di\Fake\FakeChildClass')
        );

        $actual = $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeResolveClass'));
        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual->fake);
    }

    public function testAutoResolveMissingParam()
    {
        $this->expectException('Aura\Di\Exception\MissingParam');
        $this->resolver->resolve(new Blueprint('Aura\Di\Fake\FakeParamsClass'));
    }

    public function testContainerConstructorWithDefaultParamAndTypedInjection()
    {
        $container = new Container(new InjectionFactory(new AutoResolver(new Reflector())));
        $container->types['Aura\Di\Fake\FakeInterfaceClass'] = new FakeInterfaceClass();
        $actual = $container->newInstance('Aura\Di\Fake\FakeClassWithDefaultParamInConstructor');
        $this->assertInstanceOf('Aura\Di\Fake\FakeInterfaceClass', $actual->fake);
    }

    public function testContainerConstructorWithDefaultParamAndNoTypedInjection()
    {
        $container = new Container(new InjectionFactory(new AutoResolver(new Reflector())));
        $actual = $container->newInstance('Aura\Di\Fake\FakeClassWithDefaultParamInConstructor');
        $this->assertNull($actual->fake);
    }
}
