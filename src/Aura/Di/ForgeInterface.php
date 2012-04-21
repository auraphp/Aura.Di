<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Defines the interface for Forge dependencies.
 * 
 * @package Aura.Di
 * 
 */
interface ForgeInterface
{
    /**
     * 
     * Gets the injected Config object.
     * 
     * @return ConfigInterface
     * 
     */
    public function getConfig();

    /**
     * 
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overriding params.
     * 
     * @param string $class The class to instantiate.
     * 
     * @param array $params An associative array of override parameters where
     * the key is the name of the constructor parameter and the value is the
     * parameter value to use.
     * 
     * @param array $setters An associative array of override setters where
     * the key is the name of the setter method to call and the value is the
     * value to be passed to the setter method.
     * 
     * @return object
     * 
     */
    public function newInstance($class, array $params = [], array $setters = []);
}
