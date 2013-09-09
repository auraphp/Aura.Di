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
 * Dependency injection container.
 * 
 * @package Aura.Di
 * 
 */
class Container implements ContainerInterface
{
    /**
     * 
     * A Forge object to create classes through reflection.
     * 
     * @var array
     * 
     */
    protected $forge;

    /**
     * 
     * A convenient reference to the Config::$params object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $params;

    /**
     * 
     * A convenient reference to the Config::$setter object, which itself
     * is contained by the Forge object.
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
    protected $defs = [];

    /**
     * 
     * Retains the actual service objects.
     * 
     * @var array
     * 
     */
    protected $services = [];

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
     * @param ForgeInterface $forge A forge for creating objects using
     * keyword parameter configuration.
     * 
     */
    public function __construct(ForgeInterface $forge)
    {
        $this->forge  = $forge;
        $this->params = $this->getForge()->getConfig()->getParams();
        $this->setter = $this->getForge()->getConfig()->getSetter();
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

        throw new \UnexpectedValueException($key);
    }

    /**
     * 
     * When cloning this Container, *do not* make a copy of the service
     * objects.  Leave the configuration and definitions intact.
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        $this->services = [];
        $this->forge = clone $this->forge;
    }

    /**
     * 
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     * 
     * @return void
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
     * Gets the Forge object used for creating new instances.
     * 
     * @return array
     * 
     */
    public function getForge()
    {
        return $this->forge;
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
    public function has($key)
    {
        return isset($this->defs[$key]);
    }

    /**
     * 
     * Sets a service definition by name. If you set a service as a Closure,
     * it is automatically treated as a Lazy. (Note that is has to be a
     * Closure, not just any callable, to be treated as a Lazy; this is
     * because the actual service object itself might be callable via an
     * __invoke() method.)
     * 
     * @param string $key The service key.
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
    public function set($key, $val)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }

        if (! is_object($val)) {
            throw new Exception\ServiceNotObject($key);
        }

        if ($val instanceof \Closure) {
            $val = new Lazy($val);
        }

        $this->defs[$key] = $val;

        return $this;
    }

    /**
     * 
     * Gets a service object by key, lazy-loading it as needed.
     * 
     * @param string $key The service to get.
     * 
     * @return object
     * 
     * @throws Exception\ServiceNotFound when the requested service
     * does not exist.
     * 
     */
    public function get($key)
    {
        // does the definition exist?
        if (! $this->has($key)) {
            throw new Exception\ServiceNotFound($key);
        }

        // has it been instantiated?
        if (! isset($this->services[$key])) {
            // instantiate it from its definition.
            $service = $this->defs[$key];
            // lazy-load as needed
            if ($service instanceof Lazy) {
                $service = $service();
            }
            // retain
            $this->services[$key] = $service;
        }

        // done
        return $this->services[$key];
    }

    /**
     * 
     * Gets the list of instantiated services.
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
     * Gets the list of service definitions.
     * 
     * @return array
     * 
     */
    public function getDefs()
    {
        return array_keys($this->defs);
    }

    /**
     * 
     * Returns a Lazy containing a general-purpose callable. Use this when you
     * have complex logic or heavy overhead when creating a param that may or 
     * may not need to be loaded.
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazy(function () {
     *          return include 'filename.php';
     *      });
     * 
     * @param callable $callable The callable functionality.
     * 
     * @return Lazy A lazy-load object that contains the callable.
     * 
     */
    public function lazy(callable $callable)
    {
        return new Lazy($callable);
    }

    /**
     * 
     * Returns a Lazy that gets a service. This allows you to replace the
     * following idiom ...
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazy(function() use ($di)) {
     *          return $di->get('service');
     *      }
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyGet('service');
     * 
     * @param string $key The service name; it does not need to exist yet.
     * 
     * @return Lazy A lazy-load object that gets the named service.
     * 
     */
    public function lazyGet($key)
    {
        $self = $this;
        return $this->lazy(
            function () use ($self, $key) {
                return $self->get($key);
            }
        );
    }

    /**
     * 
     * Returns a new instance of the specified class, optionally
     * with additional override parameters.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return object An instance of the requested class.
     * 
     */
    public function newInstance($class, array $params = [], array $setters = [])
    {
        return $this->forge->newInstance($class, $params, $setters);
    }

    /**
     * 
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazy(function () use ($di)) {
     *          return $di->newInstance('OtherClass', [...]);
     *      });
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyNew('OtherClass', [...]);
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance
     * 
     * @return Lazy A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew($class, array $params = [], array $setters = [])
    {
        $forge = $this->getForge();
        return $this->lazy(
            function () use ($forge, $class, $params, $setters) {
                return $forge->newInstance($class, $params, $setters);
            }
        );
    }
    
    /**
     * 
     * Returns a lazy that requires a file.  This replaces the idiom ...
     * 
     *     $di->params['ClassName']['foo'] = $di->lazy(function () {
     *         return require "/path/to/file.php";
     *     };
     * 
     * ... with:
     * 
     *     $di->params['ClassName']['foo'] = $di->lazyRequire("/path/to/file.php");
     * 
     * @param string $file The file to require.
     * 
     * @return Lazy
     * 
     */
    public function lazyRequire($file)
    {
        return $this->lazy(function () use ($file) {
            return require $file;
        });
    }

    /**
     * 
     * Returns a lazy that includes a file.  This replaces the idiom ...
     * 
     *     $di->params['ClassName']['foo'] = $di->lazy(function () {
     *         return include "/path/to/file.php";
     *     };
     * 
     * ... with:
     * 
     *     $di->params['ClassName']['foo'] = $di->lazyRequire("/path/to/file.php");
     * 
     * @param string $file The file to include.
     * 
     * @return Lazy
     * 
     */
    public function lazyInclude($file)
    {
        return $this->lazy(function () use ($file) {
            return include $file;
        });
    }

    /**
     * 
     * Returns a Lazy that invokes a callable (e.g., to call a method on an
     * object).
     * 
     * @param $callable callable The callable.  Params after this one are
     * treated as params for the call.
     * 
     * @return Lazy
     * 
     */
    public function lazyCall($callable)
    {
        // get params, if any, after removing $callable
        $params = func_get_args();
        array_shift($params);
        
        // create the closure to invoke the callable
        $call = function () use ($callable, $params) {
            
            // convert Lazy objects in the callable
            if (is_array($callable)) {
                foreach ($callable as $key => $val) {
                    if ($val instanceof Lazy) {
                        $callable[$key] = $val();
                    }
                }
            }
            
            // convert Lazy objects in the params
            foreach ($params as $key => $val) {
                if ($val instanceof Lazy) {
                    $params[$key] = $val();
                }
            }
            
            // make the call
            return call_user_func_array($callable, $params);
        };
        
        // return wrapped in a Lazy, and done
        return $this->lazy($call);
    }
    
    /**
     * 
     * Returns a Factory that creates an object over and over again (as vs
     * creating it one time like the lazyNew() or newInstance() methods).
     * 
     * @param string $class THe factory will create an instance of this class.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return Factory
     * 
     */
    public function newFactory($class, array $params = [], array $setters = [])
    {
        return new Factory($this->forge, $class, $params, $setters);
    }
}
