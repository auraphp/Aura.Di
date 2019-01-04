<?php
namespace Aura\Di\Fake;

use Aura\Di\Container;
use Aura\Di\Injection\MutationInterface;

class FakeMutationWithDependencyClass implements MutationInterface
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     *
     * Invokes the Mutation to return an object.
     *
     * @param object|FakeInterfaceClass $object
     *
     * @return object
     */
    public function __invoke(object $object): object
    {
        $object->setFoo($this->container->get('service'));
        return $object;
    }
}
