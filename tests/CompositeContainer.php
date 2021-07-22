<?php

namespace Aura\Di;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function sprintf;

class CompositeContainer implements ContainerInterface
{
    /**
     * @var array Containers that are contained within this composite container
     */
    private $containers = array();

    /**
     * @param array $containers Containers to add to this composite container
     */
    public function __construct(array $containers = array())
    {
        foreach ($containers as $container) {
            $this->addContainer($container);
        }
    }

    /**
     * Adds a container to an internal queue of containers
     *
     * @param ContainerInterface $container The container to add
     *
     * @return $this
     */
    public function addContainer(ContainerInterface $container)
    {
        $this->containers[] = $container;

        return $this;
    }

    /** {@inheritDoc} */
    public function get(string $id) {
        /** @var ContainerInterface $container */
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        throw new Exception(sprintf('Entry with id [%s] not found.', $id));
    }

    /** {@inheritDoc} */
    public function has(string $id): bool {
        /** @var ContainerInterface $container */
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }
}
