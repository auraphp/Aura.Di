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
    protected $forge;
    
    protected $class;
    
    protected $params;
    
    protected $setter;
    
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

    // func_get_args() are overrides to the overrides
    public function __invoke()
    {
        $params = array_merge($this->params, func_get_args());
        return $this->forge($this->class, $params, $this->setter);
    }
}
