<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Retains and unifies class constructor parameter values with external values.
 * 
 * @package Aura.Di
 * 
 */
interface ConfigInterface
{
    /**
     * 
     * Fetches the unified constructor values and external values.
     * 
     * @param string $class The class name to fetch values for.
     * 
     * @return array An associative array of constructor values for the class.
     * 
     */
    public function fetch($class);

    /**
     * 
     * Gets the $params property.
     * 
     * @return \ArrayObject
     * 
     */
    public function getParams();

    /**
     * 
     * Gets the $setter property.
     * 
     * @return \ArrayObject
     * 
     */
    public function getSetter();

    /**
     * 
     * Gets a retained ReflectionClass; if not already retained, creates and
     * retains one before returning it.
     *
     * @throws Exception\ServiceNotObject In case reflection could not reflect a class
     * 
     * @param string $class The class to reflect on.
     * 
     * @return \ReflectionClass
     * 
     */
    public function getReflect($class);
}
