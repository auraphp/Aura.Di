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

/**
 * 
 * Dependency injection container.
 * 
 * @package Aura.Di
 * 
 */
class Container implements ContainerInterface
{
    /**
     * 
     * A Config object to get parameters for object instantiation and
     * ReflectionClass instances.
     * 
     * @var Config
     * 
     */
    protected $config;

    /**
     * 
     * A convenient reference to the Config::$params object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $params;

    /**
     * 
     * A convenient reference to the Config::$setter object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $setter;

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
     * @param Config $config A config object for params, setters, reflects,
     * etc.
     * 
     * @param Factory $factory A factory to create support objects.
     * 
     */
    public function __construct(
        Config $config,
        Factory $factory
    ) {
        $this->config = $config;
        $this->params = $this->config->getParams();
        $this->setter = $this->config->getSetter();
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
    public function __get($key)
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
                $instance = $instance->__invoke();
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
     * Returns a Lazy containing a general-purpose callable, optionally with
     * arguments.
     * 
     * @param callable $callable The callable functionality.
     * 
     * @return Lazy A lazy-load object that contains the callable.
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
     * Returns a Lazy that gets a service.
     * 
     * @param string $service The service name; it does not need to exist yet.
     * 
     * @return LazyGet A lazy-load object that gets the named service.
     * 
     */
    public function lazyGet($service)
    {
        return $this->factory->newLazyGet($this, $service);
    }

    /**
     * 
     * Returns a Lazy that creates a new instance.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setter Override setters for the instance.
     * 
     * @return LazyInstance A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return $this->factory->newLazyInstance(
            $this,
            $class,
            $params,
            $setter
        );
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
            
            // invoke Lazy values
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
}
