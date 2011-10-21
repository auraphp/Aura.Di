<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
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
    protected $defs = array();
    
    /**
     * 
     * Retains the actual service objects.
     * 
     * @var array
     * 
     */
    protected $services = array();
    
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
        $this->services = array();
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
     * Sets a service definition by name.
     * 
     * If you set a service as a closure, it is automatically treated as a 
     * Lazy.
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object; if a Closure, is treated as a
     * Lazy.
     * 
     * @throws Exception\ContainerLocked when the Container is locked.
     * 
     * @throws Exception\Service
     *
     * @return $this
     */
    public function set($key, $val)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked;
        }
        
        if (! is_object($val)) {
            throw new Exception\ServiceInvalid($key);
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
     * Returns a Lazy that gets a service. This allows you to replace the
     * following idiom ...
     * 
     *      $di->params['ClassName']['param_name'] = new Lazy(function() use ($di)) {
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
        return new Lazy(function() use ($self, $key) {
           return $self->get($key); 
        });
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
     * @return object An instance of the requested class.
     * 
     */
    public function newInstance($class, array $params = null)
    {
        return $this->forge->newInstance($class, (array) $params);
    }
    
    /**
     * 
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     * 
     *      $di->params['ClassName']['param_name'] = Lazy(function() use ($di)) {
     *          return $di->newInstance('OtherClass', array(...));
     *      }
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyNew('OtherClass', array(...));
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @return Lazy A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew($class, array $params = null)
    {
        $forge = $this->getForge();
        return new Lazy(function() use ($forge, $class, $params) {
            return $forge->newInstance($class, $params);
        });
    }
}
