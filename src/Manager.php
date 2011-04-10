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
     * A Forge object to create classes through reflection.
     * 
     * @var array
     * 
     */
    protected $forge;
    
    /**
     * 
     * A convenient reference to the Config::$params object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $params;
    
    /**
     * 
     * A convenient reference to the Config::$setter object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $setter;
    
    /**
     * 
     * Constructor.
     * 
     * @param ForgeInterface $forge A forge for creating objects using
     * keyword parameter configuration.
     * 
     */
    public function __construct(ForgeInterface $forge)
    {
        $this->forge  = $forge;
        $this->params = $this->getForge()->getConfig()->getParams();
        $this->setter = $this->getForge()->getConfig()->getSetter();
    }
    
    /**
     * 
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     * 
     * @param string $key The property to retrieve ('params' or 'setter').
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($key == 'params' || $key == 'setter') {
            return $this->$key;
        }
        throw new \UnexpectedValueException($key);
    }
    
    /**
     * 
     * Gets the Forge object used for creating new instances.
     * 
     * @return array
     * 
     */
    public function getForge()
    {
        return $this->forge;
    }
    
    /**
     * 
     * Returns a new instance of the specified class, optionally 
     * with additional override parameters.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @return object An instance of the requested class.
     * 
     */
    public function newInstance($class, array $params = null)
    {
        return $this->forge->newInstance($class, (array) $params);
    }
    
    /**
     * 
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     * 
     *      $di->params['ClassName']['param_name'] = Lazy(function() use ($di)) {
     *          return $di->newInstance('OtherClass', array(...));
     *      }
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyNew('OtherClass', array(...));
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @return Lazy A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew($class, array $params = null)
    {
        $forge = $this->getForge();
        return new Lazy(function() use ($forge, $class, $params) {
            return $forge->newInstance($class, $params);
        });
    }
    
    /**
     * 
     * Returns a new Container object; useful for creating sub-containers
     * as services within the Manager.
     * 
     * @return Container
     * 
     */
    public function newContainer()
    {
        return new Container($this->getForge());
    }
}
