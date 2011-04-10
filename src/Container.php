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
class Container implements ContainerInterface
{
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
}
