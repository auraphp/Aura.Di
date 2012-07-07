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
 * Creates objects using reflection and the specified configuration values.
 * 
 * @package Aura.Di
 * 
 */
class Forge implements ForgeInterface
{
    /**
     * 
     * A Config object to get parameters for object instantiation and
     * \ReflectionClass instances.
     * 
     * @var Config
     * 
     */
    protected $config;
    
    /**
     * 
     * Constructor.
     * 
     * @param ConfigInterface $config A configuration object.
     * 
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
    
    /**
     * 
     * When cloning this Forge, create a separate Config object for the clone.
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        $this->config = clone $this->config;
    }
    
    /**
     * 
     * Gets the injected Config object.
     * 
     * @return ConfigInterface
     * 
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overriding params.
     * 
     * Parameters that are Lazy are invoked before instantiation.
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
    public function newInstance($class, array $params = [], array $setters = [])
    {
        list($config, $setter) = $this->config->fetch($class);
        $params = array_merge($config, (array) $params);
        
        // lazy-load params as needed
        foreach ($params as $key => $val) {
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }
        
        // merge the setters
        $setters = array_merge($setter, $setters);
        
        // create the new instance
        $call = [$this->config->getReflect($class), 'newInstance'];
        $object = call_user_func_array($call, $params);
        
        // call setters after creation
        foreach ($setters as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                // lazy-load values as needed
                if ($value instanceof Lazy) {
                    $value = $value();
                }
                // call the setter
                $object->$method($value);
            }
        }
        
        // done!
        return $object;
    }
}
