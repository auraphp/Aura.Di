<?php
/**
 * 
 * This file is part of the Aura framework for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\di;

/**
 * 
 * Retains and unifies class constructor parameter values with external values.
 * 
 * @package aura.di
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
     * Gets a retained ReflectionClass; if not already retained, creates and
     * retains one before returning it.
     * 
     * @param string $class The class to reflect on.
     * 
     * @return \ReflectionClass
     * 
     */
    public function getReflect($class);
}
