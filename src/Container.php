<?php
/**
 * 
 * This file is part of Aura for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

use Closure;
use UnexpectedValueException;
use ReflectionClass;

/**
 * 
 * Dependency injection container.
 * 
 * FUTURE NOTE: The problem with caching unified config values is that they
 * are likely to depend on $_SERVER, etc. In those cases, the values will be
 * as they were *at the time of caching* and not at the time of retrieval.
 * 
 * @package Aura.Di
 * 
 */
class Container implements ContainerInterface
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
     * An array of retained ReflectionClass instances.
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
     * Retains named service definitions.
     * 
     * @var array
     * 
     */
    protected $services = array();

    /**
     * 
     * Retains the actual service object instances.
     * 
     * @var array
     * 
     */
    protected $instances = array();

    /**
     * 
     * Is the Container locked?  (When locked, you cannot access configuration
     * properties from outside the object, and cannot set services.)
     * 
     * @var bool
     * 
     * @see __get()
     * 
     * @see set()
     * 
     */
    protected $locked = false;

    /**
     * 
     * Constructor.
     * 
     * @param Config $config A config object for params, setters, reflections,
     * etc.
     * 
     * @param Factory $factory A factory to create support objects.
     * 
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * 
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     * 
     * @param string $key The property to retrieve ('params' or 'setter').
     * 
     * @return mixed
     * 
     */
    public function &__get($key)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }

        if ($key == 'params' || $key == 'setter') {
            return $this->$key;
        }

        throw new UnexpectedValueException($key);
    }

    /**
     * 
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     * 
     * @return null
     * 
     */
    public function lock()
    {
        $this->locked = true;
    }

    /**
     * 
     * Is the Container locked?
     * 
     * @return bool
     * 
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * 
     * Does a particular service definition exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($service)
    {
        return isset($this->services[$service]);
    }

    /**
     * 
     * Sets a service definition by name. If you set a service as a Closure,
     * it is automatically treated as a Lazy. (Note that is has to be a
     * Closure, not just any callable, to be treated as a Lazy; this is
     * because the actual service object itself might be callable via an
     * __invoke() method.)
     * 
     * @param string $service The service key.
     * 
     * @param object $val The service object; if a Closure, is treated as a
     * Lazy.
     * 
     * @throws Exception\ContainerLocked when the Container is locked.
     * 
     * @throws Exception\ServiceNotObject
     * 
     * @return $this
     * 
     */
    public function set($service, $val)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }

        if (! is_object($val)) {
            throw new Exception\ServiceNotObject($service);
        }

        if ($val instanceof Closure) {
            $val = $this->factory->newLazy($val);
        }

        $this->services[$service] = $val;

        return $this;
    }

    /**
     * 
     * Gets a service object by key, lazy-loading it as needed.
     * 
     * @param string $service The service to get.
     * 
     * @return object
     * 
     * @throws Exception\ServiceNotFound when the requested service
     * does not exist.
     * 
     */
    public function get($service)
    {
        // does the definition exist?
        if (! $this->has($service)) {
            throw new Exception\ServiceNotFound($service);
        }

        // has it been instantiated?
        if (! isset($this->instances[$service])) {
            // instantiate it from its definition
            $instance = $this->services[$service];
            // lazy-load as needed
            if ($instance instanceof LazyInterface) {
                $instance = $instance();
            }
            // retain
            $this->instances[$service] = $instance;
        }

        // done
        return $this->instances[$service];
    }

    /**
     * 
     * Gets the list of instantiated services.
     * 
     * @return array
     * 
     */
    public function getInstances()
    {
        return array_keys($this->instances);
    }

    /**
     * 
     * Gets the list of service definitions.
     * 
     * @return array
     * 
     */
    public function getServices()
    {
        return array_keys($this->services);
    }

    /**
     * 
     * Returns a lazy object that calls a callable, optionally with arguments.
     * 
     * @param callable $callable The callable.
     * 
     * @return Lazy
     * 
     */
    public function lazy($callable)
    {
        $params = func_get_args();
        array_shift($params);
        return $this->factory->newLazy($callable, $params);
    }

    /**
     * 
     * Returns a lazy object that gets a service.
     * 
     * @param string $service The service name; it does not need to exist yet.
     * 
     * @return LazyGet
     * 
     */
    public function lazyGet($service)
    {
        return $this->factory->newLazyGet($this, $service);
    }

    /**
     * 
     * Returns a lazy object that creates a new instance.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setter Override setters for the instance.
     * 
     * @return LazyNew
     * 
     */
    public function lazyNew(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return $this->factory->newLazyNew($this, $class, $params, $setter);
    }
    
    /**
     * 
     * Returns a lazy that requires a file.
     * 
     * @param string $file The file to require.
     * 
     * @return LazyRequire
     * 
     */
    public function lazyRequire($file)
    {
        return $this->factory->newLazyRequire($file);
    }

    /**
     * 
     * Returns a lazy that includes a file.
     * 
     * @param string $file The file to include.
     * 
     * @return LazyInclude
     * 
     */
    public function lazyInclude($file)
    {
        return $this->factory->newLazyInclude($file);
    }

    /**
     * 
     * Returns a factory that creates an object over and over again (as vs
     * creating it one time like the lazyNew() or newInstance() methods).
     * 
     * @param string $class The factory will create an instance of this class.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return InstanceFactory
     * 
     */
    public function newFactory(
        $class,
        array $params = array(),
        array $setters = array()
    ) {
        return $this->factory->newInstanceFactory(
            $this,
            $class,
            $params,
            $setters
        );
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
        array $merge_params = array(),
        array $merge_setter = array()
    ) {
        // base configs
        list($params, $setter) = $this->getUnified($class);
        
        // merge configs
        $params = $this->mergeParams($params, $merge_params);
        $setter = array_merge($setter, $merge_setter);

        // create the new instance
        $rclass = $this->getReflection($class);
        $object = $rclass->newInstanceArgs($params);

        // call setters after creation
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
    protected function mergeParams($params, array $merge_params = array())
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
            
            // lazy-load as needed
            if ($val instanceof LazyInterface) {
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
    protected function getReflection($class)
    {
        if (! isset($this->reflection[$class])) {
            try {
                $this->reflection[$class] = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new Exception\ReflectionFailure($class, 0, $e);
            }
        }
        return $this->reflection[$class];
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
    protected function getUnified($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // fetch the values for parents so we can inherit them
        $pclass = get_parent_class($class);
        if ($pclass) {
            // parent class values
            list($parent_params, $parent_setter) = $this->getUnified($pclass);
        } else {
            // no more parents
            $parent_params = array();
            $parent_setter = array();
        }

        // stores the unified config and setter values
        $unified_params = array();
        $unified_setter = array();

        // reflect on the class
        $rclass = $this->getReflection($class);

        // does it have a constructor?
        $rctor = $rclass->getConstructor();
        if ($rctor) {
            // reflect on what params to pass, in which order
            $params = $rctor->getParameters();
            foreach ($params as $param) {
                $name = $param->name;
                $explicit = isset($this->params[$class][$name]);
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
        if (function_exists('class_uses')) {
            $uses = class_uses($class);
            foreach ($uses as $use) {
                if (isset($this->setter[$use])) {
                    $unified_setter = array_merge($this->setter[$use], $unified_setter);
                }
            }
        }

        // done, return the unified values
        $this->unified[$class][0] = $unified_params;
        $this->unified[$class][1] = $unified_setter;
        return $this->unified[$class];
    }
}
