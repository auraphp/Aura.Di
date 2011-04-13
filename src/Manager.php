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
 * Manager for all dependency injections: params, setters, services, etc.
 * 
 * @package aura.di
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
    
    public function lock()
    {
        parent::lock();
        foreach ($this->containers as $container) {
            $container->lock();
        }
    }
    
    public function newContainer($name)
    {
        if (isset($this->containers[$name])) {
            throw new Exception_ContainerExists($name);
        }
        
        $forge = clone $this->forge;
        $this->containers[$name] = new Container($forge);
        return $this->containers[$name];
    }
    
    public function getContainer($name)
    {
        if (! isset($this->containers[$name])) {
            throw new Exception_ContainerNotFound($name);
        }
        
        return $this->containers[$name];
    }
    
    public function getContainers()
    {
        return array_keys($this->containers);
    }
    
    public function cloneContainer($name)
    {
        $container = $this->getContainer($name);
        return clone $container;
    }
    
    public function lazyCloneContainer($name)
    {
        $self = $this;
        return new Lazy(function() use ($self, $name) {
            return $self->cloneContainer($name);
        });
    }
}
