<?php
namespace Aura\Di;

class ResolverAutoTest extends ResolverTest
{
    protected $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new ResolverAuto(new Reflector());
    }

    public function testMissingParam()
    {
        $actual = $this->resolver->resolve('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeParentClass', $actual->params['fake']);
    }

    public function testAutoResolveExplicit()
    {
        $this->resolver->types['Aura\Di\FakeParentClass'] = new LazyNew($this->resolver, 'Aura\Di\FakeChildClass');
        $actual = $this->resolver->resolve('Aura\Di\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\FakeChildClass', $actual->params['fake']);
    }

    public function testAutoResolveArrayAndNull()
    {
        $actual = $this->resolver->resolve('Aura\Di\FakeParamsClass');
        $this->assertSame(array(), $actual->params['array']);
        $this->assertNull($actual->params['empty']);
    }
}
