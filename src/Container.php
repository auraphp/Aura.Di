<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\di;

/**
 * 
 * Dependency injection container.
 * 
 * @package aura.di
 * 
 */
class Container
{
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
     * A Forge object to create classes through reflection.
     * 
     * @var array
     * 
     */
    protected $forge;
    
    /**
     * 
     * Retains named services.
     * 
     * @var array
     * 
     */
    protected $service = array();
    
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
        // retain for various uses
        $this->forge = $forge;
        
        // convenience properties
        $this->params = $this->getForge()->getConfig()->getParams();
        $this->setter = $this->getForge()->getConfig()->getSetter();
    }
    
    /**
     * 
     * Magic get to provide access to the Forge and the Config::$external 
     * objects.
     * 
     * @param string $key The property to retrieve ('forge' or 'config').
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($key == 'params' || $key == 'setter' || $key == 'forge') {
            return $this->$key;
        }
        throw new \UnexpectedValueException($key);
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
     * Does a particular service exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($key)
    {
        return isset($this->service[$key]);
    }
    
    /**
     * 
     * Sets a service object by name.
     * 
     * If you set a service as a closure, it is automatically treated as a 
     * Lazy.
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object; if a Closure, is treated as a
     * Lazy.
     * 
     */
    public function set($key, $val)
    {
        if (! is_object($val)) {
            throw new Exception_ServiceInvalid($key);
        }
        
        if ($val instanceof \Closure) {
            $val = new Lazy($val);
        }
        
        $this->service[$key] = $val;
    }
    
    /**
     * 
     * Gets a service object by key, lazy-loading it as needed.
     * 
     * @param string $key The service to get.
     * 
     * @return object
     * 
     * @throws \aura\di\Exception_ServiceNotFound when the requested service
     * does not exist.
     * 
     */
    public function get($key)
    {
        // does the key exist?
        if (! $this->has($key)) {
            throw new Exception_ServiceNotFound($key);
        }
        
        // invoke lazy-loading as needed
        if ($this->service[$key] instanceof Lazy) {
            $this->service[$key] = $this->service[$key]();
        }
        
        // done
        return $this->service[$key];
    }
    
    /**
     * 
     * Gets the list of services provided.
     * 
     * @return array
     * 
     */
    public function getServices()
    {
        return array_keys($this->service);
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
