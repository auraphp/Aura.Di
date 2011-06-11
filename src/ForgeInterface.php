<?php
namespace Aura\Di;
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
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     * 
     * @return object
     * 
     */
    public function newInstance($class, array $params = null);
}
