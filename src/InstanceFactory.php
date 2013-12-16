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
 * A generic factory to create multiple instances of a single class.
 * 
 * @package Aura.Di
 * 
 */
class InstanceFactory
{
    /**
     * 
     * The container.
     * 
     * @var ContainerInterface
     * 
     */
    protected $container;
    
    /**
     * 
     * The class to create.
     * 
     * @var string
     * 
     */
    protected $class;
    
    /**
     * 
     * Override params for the class.
     * 
     * @var array
     * 
     */
    protected $params;
    
    /**
     * 
     * Override setters for the class.
     * 
     * @var array
     * 
     */
    protected $setter;
    
    /**
     * 
     * Constructor.
     * 
     * @param ContainerInterface $container
     * 
     * @param string $class The class to create.
     * 
     * @param array $params Override params for the class.
     * 
     * @param array $setter Override setters for the class.
     * 
     */
    public function __construct(
        ContainerInterface $container,
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        $this->container = $container;
        $this->class = $class;
        $this->params = $params;
        $this->setter = $setter;
    }

    /**
     * 
     * Invoke this Factory object as a function to use the Container to create
     * a new instance of the specified class; pass sequential parameters as
     * as yet another set of constructor parameter overrides.
     * 
     * Why the overrides for the overrides?  So that any package that needs a
     * factory can make its own, using sequential params in a function; then
     * the factory call can be replaced by a call to this Factory.
     * 
     * @return object
     * 
     */
    public function __invoke()
    {
        $params = array_merge($this->params, func_get_args());
        return $this->container->newInstance($this->class, $params, $this->setter);
    }
}
