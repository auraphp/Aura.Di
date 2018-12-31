<?php
declare(strict_types=1);
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

use ReflectionClass;
use ReflectionException;

/**
 *
 * A serializable collection point for for Reflection data.
 *
 * @package Aura.Di
 *
 */
class Reflector
{
    /**
     *
     * Collected ReflectionClass instances.
     *
     * @var array
     *
     */
    protected $classes = [];

    /**
     *
     * Collected arrays of ReflectionParameter instances for class constructors.
     *
     * @var array
     *
     */
    protected $params = [];

    /**
     *
     * Collected traits in classes.
     *
     * @var array
     *
     */
    protected $traits = [];

    /**
     *
     * When serializing, ignore the Reflection-based properties.
     *
     * @return array
     *
     */
    public function __sleep(): array
    {
        return ['traits'];
    }

    /**
     *
     * Returns a ReflectionClass for the given class.
     *
     * @param string $class Return a ReflectionClass for this class.
     *
     * @return ReflectionClass
     *
     * @throws ReflectionException when the class does not exist.
     *
     */
    public function getClass($class): ReflectionClass
    {
        if (! isset($this->classes[$class])) {
            $this->classes[$class] = new ReflectionClass($class);
        }

        return $this->classes[$class];
    }

    /**
     *
     * Returns an array of ReflectionParameter instances for the constructor of
     * a given class.
     *
     * @param string $class Return the array of ReflectionParameter instances
     * for the constructor of this class.
     *
     * @return array|\ReflectionParameter[]
     *
     */
    public function getParams($class): array
    {
        if (! isset($this->params[$class])) {
            $this->params[$class] = [];
            $constructor = $this->getClass($class)->getConstructor();
            if ($constructor) {
                $this->params[$class] = $constructor->getParameters();
            }
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
    public function getTraits($class): array
    {
        if (! isset($this->traits[$class])) {
            $traits = [];

            // get traits from ancestor classes
            do {
                $traits += class_uses($class);
            } while ($class = get_parent_class($class));

            // get traits from ancestor traits
            $traitsToSearch = $traits;
            while (!empty($traitsToSearch)) {
                $newTraits = class_uses(array_pop($traitsToSearch));
                $traits += $newTraits;
                $traitsToSearch += $newTraits;
            };

            foreach ($traits as $trait) {
                $traits += class_uses($trait);
            }

            $this->traits[$class] = array_unique($traits);
        }

        return $this->traits[$class];
    }
}
