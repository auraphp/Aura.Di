<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

use ReflectionException;

/**
 *
 * A factory to create support objects for the Container.
 *
 * @package Aura.Di
 *
 * @property-read array $params Constructor params for classes.
 *
 * @property-read array $setter Setter definitions for classes/interfaces.
 *
 * @property-read array $values Arbitrary values.
 *
 */
class Factory
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
     * Setter definitions in the form of `$setter[$class][$method] = $value`.
     *
     * @var array
     *
     */
    protected $setter = array();

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
     * An array of retained Reflection instances.
     *
     * @var array
     *
     */
    protected $reflection = array();

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
     * Is auto-resolution enabled or disabled?
     *
     * @var bool
     *
     */
    protected $auto_resolve = true;

    /**
     *
     * Auto-resolve these typehints to these values.
     *
     * @var array
     *
     */
    protected $types = array();

    /**
     *
     * Returns a reference to various property arrays.
     *
     * @param string $key The property name to return.
     *
     * @return array
     *
     */
    public function &__get($key)
    {
        return $this->$key;
    }

    /**
     *
     * Enables and disables auto-resolution.
     *
     * @param bool $auto_resolve True to enable, false to disable.
     *
     * @return null
     *
     */
    public function setAutoResolve($auto_resolve)
    {
        $this->auto_resolve = (bool) $auto_resolve;
    }

    /**
     *
     * Returns a new InstanceFactory.
     *
     * @param Container $container The service container.
     *
     * @param string $class The class to create.
     *
     * @param array $params Override params for the class.
     *
     * @param array $setter Override setters for the class.
     *
     * @return InstanceFactory
     *
     */
    public function newInstanceFactory(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new InstanceFactory($this, $class, $params, $setter);
    }

    /**
     *
     * Returns a new Lazy.
     *
     * @param callable $callable The callable to invoke.
     *
     * @param array $params Arguments for the callable.
     *
     * @return Lazy
     *
     */
    public function newLazy($callable, array $params = array())
    {
        return new Lazy($callable, $params);
    }

    /**
     *
     * Returns a new LazyGet.
     *
     * @param Container $container The service container.
     *
     * @param string $service The service to retrieve.
     *
     * @return LazyGet
     *
     */
    public function newLazyGet(Container $container, $service)
    {
        return new LazyGet($container, $service);
    }

    /**
     *
     * Returns a new LazyInclude.
     *
     * @param string $file The file to include.
     *
     * @return LazyInclude
     *
     */
    public function newLazyInclude($file)
    {
        return new LazyInclude($file);
    }

    /**
     *
     * Returns a new LazyNew.
     *
     * @param string $class The class to instantiate.
     *
     * @param array $params Params for the instantiation.
     *
     * @param array $setter Setters for the instantiation.
     *
     * @return Lazy
     *
     */
    public function newLazyNew(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new LazyNew($this, $class, $params, $setter);
    }

    /**
     *
     * Returns a new LazyRequire.
     *
     * @param string $file The file to require.
     *
     * @return LazyRequire
     *
     */
    public function newLazyRequire($file)
    {
        return new LazyRequire($file);
    }

    /**
     *
     * Returns a new LazyValue.
     *
     * @param string $key The value key to use.
     *
     * @return LazyValue
     *
     */
    public function newLazyValue($key)
    {
        return new LazyValue($this->values, $key);
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
     * @throws Exception\SetterMethodNotFound
     *
     */
    public function newInstance(
        $class,
        array $merge_params = array(),
        array $merge_setter = array()
    ) {
        // base configs
        list($params, $setter) = $this->getUnified($class);

        // merge param configs and load lazy objects
        if ($merge_params) {
            $this->mergeParams($params, $merge_params);
        } else {
            $this->loadLazyParams($params);
        }

        // are there missing params? don't worry about it with auto-resolve.
        if (! $this->auto_resolve) {
            foreach ($params as $param) {
                if ($param instanceof MissingParam) {
                    throw new Exception\MissingParam(
                        $class. '::$' . $param->getName()
                    );
                }
            }
        }

        // create the new instance
        $rclass = $this->getReflection($class);
        if ($rclass->hasMethod('__construct')) {
            $object = $rclass->newInstanceArgs($params);
        } else {
            $object = $rclass->newInstance();
        }

        // call setters after creation
        $setter = array_merge($setter, $merge_setter);
        foreach ($setter as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                // lazy-load setter values as needed
                if ($value instanceof LazyInterface) {
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
    protected function mergeParams(&$params, array $merge_params = array())
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
    protected function loadLazyParams(&$params)
    {
        foreach ($params as $key => $val) {
            if ($val instanceof LazyInterface) {
                $params[$key] = $val();
            }
        }
    }

    /**
     *
     * Returns a Reflection for a named class.
     *
     * @param string $class The class to reflect on.
     *
     * @return Reflection
     *
     * @throws Exception\ReflectionFailure Could not reflect on the class.
     *
     */
    protected function getReflection($class)
    {
        if (isset($this->reflection[$class])) {
            return $this->reflection[$class];
        }

        try {
            $this->reflection[$class] = new Reflection($class);
        } catch (ReflectionException $e) {
            throw new Exception\ReflectionFailure($e->getMessage(), 0, $e);
        }

        return $this->reflection[$class];
    }

    /**
     *
     * Returns the unified constructor params and setter values for a class.
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
            // convert from string to array of params and setter values
            $parent = $this->getUnified($parent);
        } else {
            // convert to a pair of empty arrays for params and setter values
            $parent = array(array(), array());
        }

        // stores the unified params and setter values
        $this->unified[$class][0] = $this->getUnifiedParams($class, $parent[0]);
        $this->unified[$class][1] = $this->getUnifiedSetter($class, $parent[1]);

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
        $rclass = $this->getReflection($class);
        $rctor = $rclass->getConstructor();
        if (! $rctor) {
            // no constructor, so no need to pass params
            return array();
        }

        // reflect on what params to pass, in which order
        $unified = array();
        $rparams = $rctor->getParameters();
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

        return $this->autoResolveParam($rparam, $class, $parent, $name);
    }

    /**
     *
     * Auto-resolves a unified param.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @param string $name The param name.
     *
     * @return mixed The auto-resolved param value.
     *
     */
    protected function autoResolveParam($rparam, $class, $parent, $name)
    {
        if (! $this->auto_resolve) {
            return new MissingParam($name);
        }

        if ($rparam->isArray()) {
            // use an empty array
            return array();
        }

        $rtype = $rparam->getClass();
        if ($rtype && isset($this->types[$rtype->name])) {
            // use an explicit auto-resolution
            return $this->types[$rtype->name];
        }

        if ($rtype && $rtype->isInstantiable()) {
            // use a lazy-new-instance of the typehinted class
            return $this->newLazyNew($rtype->name);
        }

        // use a null as a placeholder
        return null;
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
    protected function getUnifiedSetter($class, array $parent)
    {
        $unified = $parent;

        // look for interface setters
        $interfaces = class_implements($class);
        foreach ($interfaces as $interface) {
            if (isset($this->setter[$interface])) {
                $unified = array_merge(
                    $this->setter[$interface],
                    $unified
                );
            }
        }

        // look for non-trait setters
        if (isset($this->setter[$class])) {
            $unified = array_merge(
                $unified,
                $this->setter[$class]
            );
        }

        // look for setters inside traits
        if (function_exists('class_uses')) {
            $uses = $this->getAllTraitsForEntity($class);
            foreach ($uses as $use) {
                if (isset($this->setter[$use])) {
                    $unified = array_merge(
                        $this->setter[$use],
                        $unified
                    );
                }
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
