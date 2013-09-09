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

use ArrayObject;
use ReflectionClass;
use ReflectionException;

/**
 * 
 * Retains and unifies class configurations.
 * 
 * @package Aura.Di
 * 
 */
class Config implements ConfigInterface
{
    /**
     * 
     * Constructor params from external configuration in the form 
     * `$params[$class][$name] = $value`.
     * 
     * @var ArrayObject
     * 
     */
    protected $params;

    /**
     * 
     * An array of retained ReflectionClass instances; this is as much for
     * the Forge as it is for Config.
     * 
     * @var array
     * 
     */
    protected $reflect = [];

    /**
     * 
     * Setter definitions in the form of `$setter[$class][$method] = $value`.
     * 
     * @var ArrayObject
     * 
     */
    protected $setter;

    /**
     * 
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and external configurations.
     * 
     * @var array
     * 
     */
    protected $unified = [];

    /**
     * 
     * Constructor.
     * 
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * 
     * When cloning this object, reset the params and setter values (but
     * leave the reflection values in place).
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        $this->reset();
    }

    /**
     * 
     * Resets the params and setter values.
     * 
     * @return void
     * 
     */
    protected function reset()
    {
        $this->params = new ArrayObject;
        $this->params['*'] = [];
        $this->setter = new ArrayObject;
        $this->setter['*'] = [];
    }

    /**
     * 
     * Gets the $params property.
     * 
     * @return ArrayObject
     * 
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 
     * Gets the $setter property.
     * 
     * @return ArrayObject
     * 
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * 
     * Returns a ReflectionClass for a named class.
     *
     * @param string $class The class to reflect on.
     * 
     * @return ReflectionClass
     * 
     * @throws Exception\ReflectionFailure Could not reflect on the class.
     * 
     */
    public function getReflect($class)
    {
        if (! isset($this->reflect[$class])) {
            try {
                $this->reflect[$class] = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new Exception\ReflectionFailure($class, 0, $e);
            }
        }
        return $this->reflect[$class];
    }

    /**
     * 
     * Fetches the unified constructor params and setter values for a class.
     * 
     * @param string $class The class name to fetch values for.
     * 
     * @return array An array with two elements; 0 is the constructor values 
     * for the class, and 1 is the setter methods and values for the class.
     * 
     */
    public function fetch($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // fetch the values for parents so we can inherit them
        $pclass = get_parent_class($class);
        if ($pclass) {
            // parent class values
            list($parent_params, $parent_setter) = $this->fetch($pclass);
        } else {
            // no more parents; get top-level values for all classes
            $parent_params = $this->params['*'];
            $parent_setter = $this->setter['*'];
        }

        // stores the unified config and setter values
        $unified_params = [];
        $unified_setter = [];

        // reflect on the class
        $rclass = $this->getReflect($class);

        // does it have a constructor?
        $rctor = $rclass->getConstructor();
        if ($rctor) {
            // reflect on what params to pass, in which order
            $params = $rctor->getParameters();
            foreach ($params as $param) {
                $name = $param->name;
                $explicit = $this->params->offsetExists($class)
                         && isset($this->params[$class][$name]);
                if ($explicit) {
                    // use the explicit value for this class
                    $unified_params[$name] = $this->params[$class][$name];
                } elseif (isset($parent_params[$name])) {
                    // use the implicit value for the parent class
                    $unified_params[$name] = $parent_params[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    // use the external value from the constructor
                    $unified_params[$name] = $param->getDefaultValue();
                } else {
                    // no value, use a null placeholder
                    $unified_params[$name] = null;
                }
            }
        }

        // merge the setters
        if (isset($this->setter[$class])) {
            $unified_setter = array_merge($parent_setter, $this->setter[$class]);
        } else {
            $unified_setter = $parent_setter;
        }

        // look for setters inside traits
        $uses = class_uses($class);
        foreach ($uses as $use) {
            if (isset($this->setter[$use])) {
                $unified_setter = array_merge($this->setter[$use], $unified_setter);
            }
        }

        // done, return the unified values
        $this->unified[$class][0] = $unified_params;
        $this->unified[$class][1] = $unified_setter;
        return $this->unified[$class];
    }
}
