<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * A generic factory to create objects of a single class.
 * 
 * @package Aura.Di
 * 
 */
class Factory
{
    /**
     * 
     * An object forge.
     * 
     * @var ForgeInterface
     * 
     */
    protected $forge;
    
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
     * @param ForgeInterface $forge
     * 
     * @param string $class The class to create.
     * 
     * @param array $params Override params for the class.
     * 
     * @param array $setter Override setters for the class.
     * 
     */
    public function __construct(
        ForgeInterface $forge,
        $class,
        array $params = [],
        array $setter = []
    ) {
        $this->forge = $forge;
        $this->class = $class;
        $this->params = $params;
        $this->setter = $setter;
    }

    /**
     * 
     * Invoke this Factory object as a function to use the Forge to create a
     * new instance of the specified class; pass sequential parameters as
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
        return $this->forge->newInstance($this->class, $params, $this->setter);
    }
}
