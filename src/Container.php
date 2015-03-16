<?php
/**
 *
 * This file is part of Aura for PHP.
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
 * @property-read array $params A reference to the Factory $params.
 *
 * @property-read array $setter A reference to the Factory $setter.
 *
 * @property-read array $setters A reference to the Factory $setter.
 *
 * @property-read array $types A reference to the Factory $types.
 *
 * @property-read array $values A reference to the Factory $values.
 *
 */
class Container implements ContainerInterface
{
    /**
     *
     * A factory to create objects.
     *
     * @var Factory
     *
     */
    protected $factory;

    /**
     *
     * A reference to the Factory $params.
     *
     * @var array
     *
     */
    protected $params;

    /**
     *
     * An arbitrary set of values.
     *
     * @var array
     *
     */
    protected $values;

    /**
     *
     * A reference to the Factory $setter.
     *
     * @var array
     *
     */
    protected $setter;

    /**
     *
     * A reference to the Factory $types.
     *
     * @var array
     *
     */
    protected $types;

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
     * A map of magic __get() names and their underlying properties.
     *
     * @var array
     *
     */
    protected $magic_props = array(
        'param' => 'params',
        'params' => 'params',
        'setter' => 'setter',
        'setters' => 'setter',
        'value' => 'values',
        'values' => 'values',
        'type' => 'types',
        'types' => 'types'
    );

    /**
     *
     * Constructor.
     *
     * @param Factory $factory A factory to create objects.
     *
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
        $this->params =& $this->factory->params;
        $this->setter =& $this->factory->setter;
        $this->values =& $this->factory->values;
        $this->types =& $this->factory->types;
    }

    /**
     *
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     *
     * @param string $key The property to retrieve ('params' or 'setter(s)').
     *
     * @return mixed
     *
     * @throws Exception\ContainerLocked
     *
     * @throws \UnexpectedValueException
     *
     */
    public function &__get($key)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }

        if (isset($this->magic_props[$key])) {
            $prop = $this->magic_props[$key];
            return $this->$prop;
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
     * Enables and disables auto-resolution.
     *
     * @param bool $auto_resolve True to enable, false to disable.
     *
     * @return null
     *
     */
    public function setAutoResolve($auto_resolve)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }

        $this->factory->setAutoResolve($auto_resolve);
    }

    /**
     *
     * Does a particular service definition exist?
     *
     * @param string $service The service key to look up.
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
     * @param object|callable $val The service object; if a Closure, is treated as a
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
        if (isset($this->instances[$service])) {
            return $this->instances[$service];
        }

        // instantiate it from its definition
        $instance = $this->services[$service];

        // lazy-load as needed
        if ($instance instanceof LazyInterface) {
            $instance = $instance();
        }

        // retain and return
        $this->instances[$service] = $instance;
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
        return $this->factory->newLazyNew($class, $params, $setter);
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
     * Returns a lazy for an arbitrary value.
     *
     * @param string $key The arbitrary value key.
     *
     * @return LazyValue
     *
     */
    public function lazyValue($key)
    {
        return $this->factory->newLazyValue($key);
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
     * @throws Exception\SetterMethodNotFound
     *
     */
    public function newInstance(
        $class,
        array $merge_params = array(),
        array $merge_setter = array()
    ) {
        return $this->factory->newInstance(
            $class,
            $merge_params,
            $merge_setter
        );
    }
}
