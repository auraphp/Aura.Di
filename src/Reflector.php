<?php
namespace Aura\Di;

use ReflectionClass;
use ReflectionException;

class Reflector
{
    protected $reflection = [];

    public function __sleep()
    {
        return array();
    }

    public function get($class)
    {
        if (isset($this->reflection[$class])) {
            return $this->reflection[$class];
        }

        try {
            $this->reflection[$class] = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\ReflectionFailure($class, 0, $e);
        }

        return $this->reflection[$class];
    }
}
