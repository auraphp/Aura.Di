<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\di;

/**
 * 
 * Creates objects using reflection and the specified configuration values.
 * 
 * @package aura.di
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
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     * 
     * @return object
     * 
     */
    public function newInstance($class, array $params = null)
    {
        $params = array_merge($this->config->fetch($class), (array) $params);
        
        // lazy-load objects as needed
        foreach ($params as $key => $val) {
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }
        
        // return a new instance
        return call_user_func_array(
            array($this->config->getReflect($class), 'newInstance'),
            $params
        );
    }
}
