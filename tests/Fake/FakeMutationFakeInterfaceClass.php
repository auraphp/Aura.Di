<?php
namespace Aura\Di\Fake;

use Aura\Di\Injection\MutationInterface;

class FakeMutationFakeInterfaceClass implements MutationInterface
{
    private $fooValue;

    public function __construct($fooValue)
    {
        $this->fooValue = $fooValue;
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
        $object->setFoo($this->fooValue);
        return $object;
    }
}
