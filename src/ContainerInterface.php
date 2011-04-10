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
 * Interface for dependency injection containers.
 * 
 * @package aura.di
 * 
 */
interface ContainerInterface
{
    /**
     * 
     * Does a particular service exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($key);
    
    /**
     * 
     * Sets a service object by name.
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object.
     * 
     */
    public function set($key, $val);
    
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
    public function get($key);
    
    /**
     * 
     * Gets the list of services provided.
     * 
     * @return array
     * 
     */
    public function getServices();
    
    /**
     * 
     * Returns a Lazy that gets a service.
     * 
     * @param string $key The service name; it does not need to exist yet.
     * 
     * @return Lazy A lazy-load object that gets the named service.
     * 
     */
    public function lazyGet($key);
}
