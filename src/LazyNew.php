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

use Aura\Di\Container;

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
     * The container.
     * 
     * @var Container
     * 
     */
    protected $container;

    /**
     * 
     * The class to instantiate.
     * 
     * @var string
     * 
     */
    protected $class;
    
    /**
     * 
     * Params for the instantiation.
     * 
     * @var array
     * 
     */
    protected $params = array();
    
    /**
     * 
     * Setters for the instantiation.
     * 
     * @var array
     * 
     */
    protected $setters = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param Container $container The service container.
     * 
     * @param string $class The class to instantiate.
     * 
     * @param array $params Params for the instantiation.
     * 
     * @param array $setter Setters for the instantiation.
     * 
     */
    public function __construct(Container $container, $class, array $params, array $setters)
    {
        $this->container = $container;
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
        return $this->container->newInstance(
            $this->class,
            $this->params,
            $this->setters
        );
    }
}
