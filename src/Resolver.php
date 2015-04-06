<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use ReflectionParameter;
use UnexpectedValueException;

/**
 *
 * Resolves class creation specifics based on constructor params and setter
 * definitions, unified across class defaults, inheritance hierarchies, and
 * configuration.
 *
 * @package Aura.Di
 *
 */
class Resolver
{
    /**
     *
     * Constructor params in the form `$params[$class][$name] = $value`.
     *
     * @var array
     *
     */
    protected $params = array();

    /**
     *
     * Setter definitions in the form of `$setters[$class][$method] = $value`.
     *
     * @var array
     *
     */
    protected $setters = array();

    /**
     *
     * Arbitrary values in the form of `$values[$key] = $value`.
     *
     * @var array
     *
     */
    protected $values = array();

    /**
     *
     * A Reflector.
     *
     * @var Reflector
     *
     */
    protected $reflector = array();

    /**
     *
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and configuration.
     *
     * @var array
     *
     */
    protected $unified = array();

    /**
     *
     * Constructor.
     *
     * @param Reflector $reflector A collection point for Reflection data.
     *
     */
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     *
     * Returns a reference to various property arrays.
     *
     * @param string $key The property name to return.
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     *
     */
    public function &__get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
        throw new UnexpectedValueException($key);
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
     * @param array $merge_setters An array of override setters; the key is the
     * name of the setter method to call and the value is the value to be
     * passed to the setter method.
     *
     * @return object
     *
     * @throws Exception\SetterMethodNotFound
     *
     */
    public function resolve(
        $class,
        array $merge_params = array(),
        array $merge_setters = array()
    ) {
        list($params, $setters) = $this->getUnified($class);
        $this->mergeParams($class, $params, $merge_params);
        $this->mergeSetters($class, $setters, $merge_setters);
        return (object) [
            'reflection' => $this->reflector->getClass($class),
            'params' => $params,
            'setters' => $setters,
        ];
    }

    /**
     *
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param string $class The setters are on this class.
     *
     * @param array $setters The class setters.
     *
     * @param array $merge_setters Override with these setters.
     *
     * @return null
     *
     */
    protected function mergeSetters($class, &$setters, array $merge_setters = array())
    {
        $setters = array_merge($setters, $merge_setters);
        foreach ($setters as $method => $value) {
            if (! method_exists($class, $method)) {
                throw new Exception\SetterMethodNotFound("$class::$method");
            }
            if ($value instanceof LazyInterface) {
                $setters[$method] = $value();
            }
        }
    }

    /**
     *
     * Merges the params with overides; also invokes Lazy values.
     *
     * @param string $class The params are on this class.
     *
     * @param array $params The constructor parameters.
     *
     * @param array $merge_params An array of override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     *
     */
    protected function mergeParams($class, &$params, array $merge_params = array())
    {
        if (! $merge_params) {
            $this->mergeParamsEmpty($class, $params);
        }

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

            // is the param missing?
            if ($val instanceof ParamPlaceholder) {
                throw new Exception\MissingParam($val->getName($class));
            }

            // load lazy objects as we go
            if ($val instanceof LazyInterface) {
                $val = $val();
            }

            // retain the merged value
            $params[$key] = $val;

            // next position
            $pos += 1;
        }
    }

    /**
     *
     * Load the Lazy values in params when the merge_params are empty.
     *
     * @param string $class The params are on this class.
     *
     * @param array $params The constructor parameters.
     *
     */
    protected function mergeParamsEmpty($class, &$params)
    {
        foreach ($params as $key => $val) {
            // is the param missing?
            if ($val instanceof ParamPlaceholder) {
                throw new Exception\MissingParam($val->getName($class));
            }
            // load lazy objects as we go
            if ($val instanceof LazyInterface) {
                $params[$key] = $val();
            }
        }
    }

    /**
     *
     * Returns the unified constructor params and setters for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @return array An array with two elements; 0 is the constructor params
     * for the class, and 1 is the setter methods and values for the class.
     *
     */
    public function getUnified($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // fetch the values for parents so we can inherit them
        $spec = array(array(), array());
        $parent = get_parent_class($class);
        if ($parent) {
            $spec = $this->getUnified($parent);
        }

        // stores the unified params and setters
        $this->unified[$class][0] = $this->getUnifiedParams($class, $spec[0]);
        $this->unified[$class][1] = $this->getUnifiedSetters($class, $spec[1]);

        // done, return the unified values
        return $this->unified[$class];
    }

    /**
     *
     * Returns the unified constructor params for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return array The unified params.
     *
     */
    protected function getUnifiedParams($class, array $parent)
    {
        // reflect on what params to pass, in which order
        $unified = array();
        $rparams = $this->reflector->getParams($class);
        foreach ($rparams as $rparam) {
            $unified[$rparam->name] = $this->getUnifiedParam(
                $rparam,
                $class,
                $parent
            );
        }

        // done
        return $unified;
    }

    /**
     *
     * Returns a unified param.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The unified param value.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, $class, $parent)
    {
        $name = $rparam->name;

        // is there a value explicitly from the current class?
        $explicit = isset($this->params[$class][$name])
                 && ! $this->params[$class][$name] instanceof ParamPlaceholder;
        if ($explicit) {
            return $this->params[$class][$name];
        }

        // is there a value implicitly inherited from the parent class?
        $implicit = isset($parent[$name])
                 && ! $parent[$name] instanceof ParamPlaceholder;
        if ($implicit) {
            return $parent[$name];
        }

        // is a default value available for the current class?
        if ($rparam->isDefaultValueAvailable()) {
            return $rparam->getDefaultValue();
        }

        // param is missing
        return new ParamPlaceholder($name);
    }

    /**
     *
     * Returns the unified setters for a class.
     *
     * Class-specific setters take precendence over trait-based setters, which
     * take precedence over interface-based setters.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified setters.
     *
     * @return array The unified setters.
     *
     */
    protected function getUnifiedSetters($class, array $parent)
    {
        $unified = $parent;

        // look for interface setters
        $interfaces = class_implements($class);
        foreach ($interfaces as $interface) {
            if (isset($this->setters[$interface])) {
                $unified = array_merge(
                    $this->setters[$interface],
                    $unified
                );
            }
        }

        // look for trait setters
        $traits = $this->reflector->getTraits($class);
        foreach ($traits as $trait) {
            if (isset($this->setters[$trait])) {
                $unified = array_merge(
                    $this->setters[$trait],
                    $unified
                );
            }
        }

        // look for class setters
        if (isset($this->setters[$class])) {
            $unified = array_merge(
                $unified,
                $this->setters[$class]
            );
        }

        // done
        return $unified;
    }
}
