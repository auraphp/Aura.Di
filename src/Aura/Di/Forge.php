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
     * the configuration parameters, optionally with overrides, invoking Lazy
     * values along the way.
     * 
     * @param string $class The class to instantiate.
     * 
     * @param array $merge_params An array of override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     * 
     * @param array $merge_setter An array of override setters; the key is the
     * name of the setter method to call and the value is the value to be 
     * passed to the setter method.
     * 
     * @return object
     * 
     */
    public function newInstance(
        $class,
        array $merge_params = [],
        array $merge_setter = []
    ) {
        // base configs
        list($params, $setter) = $this->config->fetch($class);
        
        // merge configs
        $params = $this->mergeParams($params, $merge_params);
        $setter = array_merge($setter, $merge_setter);

        // create the new instance
        $rclass = $this->config->getReflect($class);
        $object = $rclass->newInstanceArgs($params);

        // call setters after creation
        foreach ($setter as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                // lazy-load setter values as needed
                if ($value instanceof Lazy) {
                    $value = $value();
                }
                // call the setter
                $object->$method($value);
            } else {
                throw new Exception\SetterMethodNotFound("$class::$method");
            }
        }
        
        // done!
        return $object;
    }
    
    /**
     * 
     * Returns the params after merging with overides; also invokes Lazy param
     * values.
     * 
     * @param array $params The constructor parameters.
     * 
     * @param array $merge_params An array of override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     * 
     * @return array
     * 
     */
    protected function mergeParams($params, array $merge_params = [])
    {
        $pos = 0;
        foreach ($params as $key => $val) {
            
            // positional overrides take precedence over named overrides
            if (array_key_exists($pos, $merge_params)) {
                // positional override
                $val = $merge_params[$pos];
            } elseif (array_key_exists($key, $merge_params)) {
                // named override
                $val = $merge_params[$key];
            }
            
            // invoke Lazy values
            if ($val instanceof Lazy) {
                $val = $val();
            }
            
            // retain the merged value
            $params[$key] = $val;
            
            // next position
            $pos += 1;
        }
        
        // done
        return $params;
    }
}
