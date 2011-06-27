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
 * Manager for multiple DI containers; the Manager is itself the main
 * container, and it provides access to sub-containers.
 * 
 * @package Aura.Di
 * 
 */
class Manager extends Container
{
    /**
     * 
     * Sub-container definitions.
     * 
     * @var array
     * 
     */
    protected $containers = array();
    
    /**
     * 
     * Locks the main container and all sub-containers; once locked, they
     * cannot be unlocked.
     * 
     * @return void
     * 
     */
    public function lock()
    {
        parent::lock();
        foreach ($this->containers as $container) {
            $container->lock();
        }
    }
    
    /**
     * 
     * Creates and retains a new sub-container.  The new container does not
     * inherit configuration from the main container in any way; you will have
     * to add params, setters, etc. on the new container.
     * 
     * @param string $name The sub-container name.
     * 
     * @return Container The new sub-container.
     * 
     */
    public function newContainer($name)
    {
        if (isset($this->containers[$name])) {
            throw new Exception\ContainerExists($name);
        }
        
        $forge = clone $this->forge;
        $this->containers[$name] = new Container($forge);
        return $this->getContainer($name);
    }
    
    /**
     * 
     * Gets a sub-container by name.
     * 
     * @param string $name The sub-container name.
     * 
     * @return Container The sub-container.
     * 
     */
    public function getContainer($name)
    {
        if (! isset($this->containers[$name])) {
            throw new Exception\ContainerNotFound($name);
        }
        
        return $this->containers[$name];
    }
    
    /**
     * 
     * Gets the names of all sub-containers.
     * 
     * @return array The names of all sub-containers.
     * 
     */
    public function getContainers()
    {
        return array_keys($this->containers);
    }
    
    /**
     * 
     * Returns a clone of a sub-container.  The clone will have all the
     * configuration and service definitions of the origin container, but it
     * will not have any of the service objects.  Calling get() will create
     * new services that are independent and separate from the origin
     * container services.
     * 
     * @param string $name The sub-container name.
     * 
     * @return Container A clone of the named sub-container.
     * 
     */
    public function cloneContainer($name)
    {
        $container = $this->getContainer($name);
        return clone $container;
    }
    
    /**
     * 
     * Returns a Lazy that, when invoked, will return a sub-container clone.
     * 
     * @param string $name The sub-container name.
     * 
     * @return Lazy
     * 
     */
    public function lazyCloneContainer($name)
    {
        $self = $this;
        return new Lazy(function() use ($self, $name) {
            return $self->cloneContainer($name);
        });
    }
}
