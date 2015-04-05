<?php
namespace Aura\Di;

use ReflectionClass;
use ReflectionException;

class Reflector
{
    protected $classes = [];

    protected $params = [];

    public function __sleep()
    {
        return array();
    }

    public function getClass($class)
    {
        if (isset($this->classes[$class])) {
            return $this->classes[$class];
        }

        try {
            $this->classes[$class] = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\ReflectionFailure($class, 0, $e);
        }

        return $this->classes[$class];
    }

    public function getParams($class)
    {
        if (isset($this->params[$class])) {
            return $this->params[$class];
        }

        $this->params[$class] = [];
        $constructor = $this->getClass($class)->getConstructor();
        if ($constructor) {
            $this->params[$class] = $constructor->getParameters();
        }

        return $this->params[$class];
    }
}
