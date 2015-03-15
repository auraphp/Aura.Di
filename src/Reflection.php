<?php

namespace Aura\Di;

use ReflectionClass;

class Reflection implements \Serializable
{
    /**
     * The class that we are reflecting.
     *
     * @var string
     */
    protected $class;

    /**
     * The reflection class
     *
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * The Constructor for our custom reflection class.
     *
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->reflection = new ReflectionClass($class);
    }

    /**
     * We want to pass-thru to the Reflection classes.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(!($this->reflection instanceof ReflectionClass)) {
            $this->reflection = new ReflectionClass($this->class);
        }

        return call_user_func_array([$this->reflection, $name], $arguments);
    }

    public function serialize()
    {
        $this->reflection = null;
        return serialize($this->class);
    }

    public function unserialize($serialized)
    {
        $class = unserialize($serialized);
        $this->class = $class;
    }
}