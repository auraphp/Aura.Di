<?php
namespace Aura\Di;

use Aura\Di\Resolver\Resolver;
use Aura\Di\Resolver\Reflector;

class InstanceFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new Resolver(new Reflector());
    }

    protected function newInstanceFactory(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return new InstanceFactory($this->resolver, $class, $params, $setters);
    }

    public function test__invoke()
    {
        $factory = new Factory(new Resolver(new Reflector()));
        $other = $factory->newInstance('Aura\Di\Fake\FakeOtherClass');

        $factory = $this->newInstanceFactory(
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
