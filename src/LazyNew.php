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

/**
 * 
 * Wraps a callable specifically for the purpose of lazy-loading an object.
 * 
 * @package Aura.Di
 * 
 */
class LazyNew implements LazyInterface
{
    /**
     * 
     * The object forge.
     * 
     * @var Forge
     * 
     */
    protected $forge;

    /**
     * 
     * The class to instantiate.
     * 
     * @var string
     * 
     */
    protected $class;
    
    protected $params = [];
    
    protected $setters = [];
    
    /**
     * 
     * Constructor.
     * 
     * @param Container $container The service container.
     * 
     * @param string $service The service to retrieve.
     * 
     * @return null
     * 
     */
    public function __construct(Forge $forge, $class, array $params, array $setters)
    {
        $this->forge = $forge;
        $this->class = $class;
        $this->params = $params;
        $this->setters = $setters;
    }

    /**
     * 
     * Invokes the closure to create the instance.
     * 
     * @return object The object created by the closure.
     * 
     */
    public function __invoke()
    {
        return $this->forge->newInstance($this->class, $this->params, $this->setters);
    }
}
