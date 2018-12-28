<?php

namespace Aura\Di;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MinimalContainer implements ContainerInterface
{
    private $collection = [];
    private $delegateContainer;

    public function __construct(array $services, ContainerInterface $delegateContainer = null)
    {
        $this->collection = $services;
        $this->delegateContainer = $delegateContainer;
    }

    public function get($id)
    {
        if (isset($this->collection[$id])) {
            if (is_callable($this->collection[$id])) {
                $this->collection[$id] = call_user_func($this->collection[$id], $this);
            }

            return $this->collection[$id];
        }

        if ($this->delegateContainer && $this->delegateContainer->has($id)) {
            return $this->delegateContainer->get($id);
        }

        throw new class extends \UnexpectedValueException implements NotFoundExceptionInterface {};
    }

    public function has($id)
    {
        return isset($this->collection[$id]);
    }
}
