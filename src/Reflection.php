<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

use ReflectionClass;
use Serializable;

/**
 *
 * A ReflectionClass decorator that provides serialization that re-instantiates
 * the underlying ReflectionClass on demand.
 *
 * @package Aura.Di
 *
 * @method ReflectionMethod getConstructor()
 * @method bool hasMethod(string $name)
 * @method object newInstanceArgs(array $args)
 * @method object newInstance()
 *
 */
class Reflection implements Serializable
{
    /**
     *
     * The class on which we are reflecting.
     *
     * @var string
     *
     */
    protected $class;

    /**
     *
     * The decorated ReflectionClass instance.
     *
     * @var ReflectionClass
     *
     */
    protected $reflection;

    /**
     *
     * Constructor.
     *
     * @param string $class The class on which we are reflecting.
     *
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->setReflection();
    }

    /**
     *
     * Pass-through to decorated ReflectionClass methods.
     *
     * @param string $name The method name.
     *
     * @param array $args The method arguments.
     *
     * @return mixed
     *
     */
    public function __call($name, $args)
    {
        $this->setReflection();
        return call_user_func_array(array($this->reflection, $name), $args);
    }

    /**
     *
     * Sets the decorated ReflectionClass instance.
     *
     * @return null
     *
     */
    protected function setReflection()
    {
        if (! $this->reflection) {
            $this->reflection = new ReflectionClass($this->class);
        }
    }

    /**
     *
     * Implements Serializer::serialize().
     *
     * @return string The serialized string.
     *
     */
    public function serialize()
    {
        $this->reflection = null;
        return serialize($this->class);
    }

    /**
     *
     * Implements Serializer::unserialize().
     *
     * @param string $serialized The serialized string.
     *
     * @return null
     *
     */
    public function unserialize($serialized)
    {
        $class = unserialize($serialized);
        $this->class = $class;
    }
}
