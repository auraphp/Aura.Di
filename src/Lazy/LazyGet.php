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
namespace Aura\Di\Lazy;

use Aura\Di\Container;

/**
 * 
 * Wraps a callable specifically for the purpose of lazy-loading an object.
 * 
 * @package Aura.Di
 * 
 */
class LazyGet implements LazyInterface
{
    /**
     * 
     * The service container.
     * 
     * @var Container
     * 
     */
    protected $container;

    /**
     * 
     * The service name to retrieve.
     * 
     * @var string
     * 
     */
    protected $service;
    
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
    public function __construct(Container $container, $service)
    {
        $this->container = $container;
        $this->service = $service;
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
        return $this->container->get($this->service);
    }
}
