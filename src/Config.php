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
class Config implements ConfigInterface
{
    /**
     * 
     * Values loaded from external source.
     * 
     * @var \ArrayObject
     * 
     */
    protected $external;
    
    /**
     * 
     * Constructor values unified with external values.
     * 
     * @var array
     * 
     */
    protected $unified = array();
    
    /**
     * 
     * An array of retained ReflectionClass instances; this is as much for
     * the Forge as it is for Config.
     * 
     * @var array
     * 
     */
    protected $reflect = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param \ArrayObject $external An ArrayObject to retain the external
     * config values.
     * 
     */
    public function __construct(\ArrayObject $external)
    {
        $this->setExternal($external);
    }
    
    /**
     * 
     * Sets the $external property, adding a '*' entry if one does not
     * already exist.
     * 
     * @param \ArrayObject $external An ArrayObject to retain the external
     * config values.
     * 
     * @return void
     * 
     */
    public function setExternal(\ArrayObject $external)
    {
        $this->external = $external;
        if (! isset($this->external['*'])) {
            $this->external['*'] = array();
        }
        $this->unified = array();
    }
    
    /**
     * 
     * Gets the $external property.
     * 
     * @param \ArrayObject
     * 
     */
    public function getExternal()
    {
        return $this->external;
    }
    
    /**
     * 
     * Returns a \ReflectionClass for a named class.
     * 
     * @param string $class The class to reflect on.
     * 
     * @return \ReflectionClass
     * 
     */
    public function getReflect($class)
    {
        if (! isset($this->reflect[$class])) {
            $this->reflect[$class] = new \ReflectionClass($class);
        }
        return $this->reflect[$class];
    }
    
    /**
     * 
     * Fetches the unified constructor values and external values.
     * 
     * @param string $class The class name to fetch values for.
     * 
     * @return array An associative array of constructor values for the class.
     * 
     */
    public function fetch($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }
        
        // fetch the external values for parents so we can inherit them
        $pclass = get_parent_class($class);
        if ($pclass) {
            // parent class values
            $parent = $this->fetch($pclass);
        } else {
            // no more parents; get top-level values for all classes
            $parent = $this->external['*'];
        }
        
        // stores the unified values
        $unified = array();
        
        // reflect on the class
        $rclass = $this->getReflect($class);
        
        // does it have a constructor?
        $rctor = $rclass->getConstructor();
        if ($rctor) {
            // reflect on what params to pass, in which order
            $params = $rctor->getParameters();
            foreach ($params as $param) {
                $name = $param->name;
                $explicit = $this->external->offsetExists($class)
                         && isset($this->external[$class][$name]);
                if ($explicit) {
                    // use the explicit value for this class
                    $unified[$name] = $this->external[$class][$name];
                } elseif (isset($parent[$name])) {
                    // use the implicit value for the parent class
                    $unified[$name] = $parent[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    // use the external value from the constructor
                    $unified[$name] = $param->getDefaultValue();
                } else {
                    // no value, use a null placeholder
                    $unified[$name] = null;
                }
            }
        }
        
        // done, return the unified values
        $this->unified[$class] = $unified;
        return $this->unified[$class];
    }
}
