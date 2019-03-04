<?php
namespace Aura\Di\Injection;

use Aura\Di\Resolver\Blueprint;
use Aura\Di\Resolver\Resolver;
use Aura\Di\Resolver\Reflector;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    protected $resolver;

    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new Resolver(new Reflector());
    }

    protected function newFactory(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return new Factory($this->resolver, new Blueprint($class, $params, $setters));
    }

    public function test__invoke()
    {
        $factory = new InjectionFactory(new Resolver(new Reflector()));
        $other = $factory->newInstance(new Blueprint('Aura\Di\Fake\FakeOtherClass'));

        $factory = $this->newFactory(
            'Aura\Di\Fake\FakeChildClass',
            [
                'foo' => 'foofoo',
                'zim' => $other,
            ],
            [
                'setFake' => 'fakefake',
            ]
        );

        $actual = $factory();

        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual);
        $this->assertInstanceOf('Aura\Di\Fake\FakeOtherClass', $actual->getZim());
        $this->assertSame('foofoo', $actual->getFoo());
        $this->assertSame('fakefake', $actual->getFake());


        // create another one, should not be the same
        $extra = $factory();
        $this->assertNotSame($actual, $extra);
    }
}
