<?php
namespace Aura\Di;

use ReflectionException;
use UnexpectedValueException;

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
        $this->mergeParams($params, $merge_params);
        $this->mergeSetters($class, $setters, $merge_setters);
        return (object) [
            'reflection' => $this->reflector->getClass($class),
            'params' => $params,
            'setters' => $setters,
        ];
    }

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
    protected function mergeParams(&$params, array $merge_params = array())
    {
        if (! $merge_params) {
            return $this->mergeParamsEmpty($params);
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
            if ($val instanceof MissingParam) {
                throw new Exception\MissingParam($val->getName());
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
     * Loads the lazy object in an array of params.
     *
     * @param array $params An array of params.
     *
     * @return null
     *
     */
    protected function mergeParamsEmpty(&$params)
    {
        foreach ($params as $key => $val) {
            // is the param missing?
            if ($val instanceof MissingParam) {
                throw new Exception\MissingParam($val->getName());
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
        $parent = get_parent_class($class);
        if ($parent) {
            // convert from string to array of params and setters
            $parent = $this->getUnified($parent);
        } else {
            // convert to a pair of empty arrays for params and setters
            $parent = array(array(), array());
        }

        // stores the unified params and setters
        $this->unified[$class][0] = $this->getUnifiedParams($class, $parent[0]);
        $this->unified[$class][1] = $this->getUnifiedSetters($class, $parent[1]);

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
            $name = $rparam->name;
            $unified[$name] = $this->getUnifiedParam(
                $rparam,
                $class,
                $parent,
                $name
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
     * @param string $name The param name.
     *
     * @return mixed The unified param value.
     *
     */
    protected function getUnifiedParam($rparam, $class, $parent, $name)
    {
        $explicit = isset($this->params[$class][$name])
                 && ! $this->params[$class][$name] instanceof MissingParam;
        if ($explicit) {
            // use the explicit value for this class
            return $this->params[$class][$name];
        }

        $implicit = isset($parent[$name])
                 && ! $parent[$name] instanceof MissingParam;
        if ($implicit) {
            // use the implicit value from the parent class
            return $parent[$name];
        }

        if ($rparam->isDefaultValueAvailable()) {
            // use the default value
            return $rparam->getDefaultValue();
        }

        // param is missing
        return new MissingParam($class, $name);
    }

    /**
     *
     * Returns the unified setters for a class.
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

        // look for non-trait setters
        if (isset($this->setters[$class])) {
            $unified = array_merge(
                $unified,
                $this->setters[$class]
            );
        }

        // look for setters inside traits
        $uses = $this->getAllTraitsForEntity($class);
        foreach ($uses as $use) {
            if (isset($this->setters[$use])) {
                $unified = array_merge(
                    $this->setters[$use],
                    $unified
                );
            }
        }

        // done
        return $unified;
    }

    /**
     *
     * Returns all traits used by a class and its ancestors,
     * and the traits used by those traits' and their ancestors.
     *
     * @param string|object $entity The class or trait to look at for used traits.
     *
     * @return array All traits used by the requested class or trait.
     *
     */
    protected function getAllTraitsForEntity($entity)
    {
        $traits = array();

        // get traits from ancestor classes
        do {
            $traits += class_uses($entity);
        } while ($entity = get_parent_class($entity));

        // get traits from ancestor traits
        while (list($trait) = each($traits)) {
            foreach (class_uses($trait) as $key => $name) {
                $traits[$key] = $name;
            }
        }

        return $traits;
    }
}