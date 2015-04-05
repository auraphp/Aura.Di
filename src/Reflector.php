<?php
namespace Aura\Di;

use ReflectionClass;
use ReflectionException;

class Reflector
{
    protected $classes = [];

    protected $params = [];

    protected $traits = [];

    public function __sleep()
    {
        return array('traits');
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


    /**
     *
     * Returns all traits used by a class and its ancestors,
     * and the traits used by those traits' and their ancestors.
     *
     * @param string $class The class or trait to look at for used traits.
     *
     * @return array All traits used by the requested class or trait.
     *
     * @todo Make this function recursive so that parent traits are retained
     * in the parent keys.
     *
     */
    public function getTraits($class)
    {
        if (isset($this->traits[$class])) {
            return $this->traits[$class];
        }

        $traits = array();

        // get traits from ancestor classes
        do {
            $traits += class_uses($class);
        } while ($class = get_parent_class($class));

        // get traits from ancestor traits
        while (list($trait) = each($traits)) {
            foreach (class_uses($trait) as $key => $name) {
                $traits[$key] = $name;
            }
        }

        $this->traits[$class] = $traits;
        return $this->traits[$class];
    }
}
