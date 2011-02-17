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
 * Dependency injection container.
 * 
 * <?php
 *      // instantiate the container so we have it available for closures
 *      $di = new Container(new Forge(new Config(new \ArrayObject)));
 *      
 *      // define a service inside a closure; note that this will not create
 *      // any objects until you get the service from the container.
 *      $di->set('db', function() use ($di) {
 *          return new DatabaseConnection(
 *              'mysql:host=localhost;dbname=example',
 *              'username',
 *              'password'
 *          );
 *      });
 *      
 *      // use the container in another service; again, this service is
 *      // defined in a closure, so it is not loaded until you get it from
 *      // the container.
 *      $di->set('some_model', function() use ($di) {
 *          // ModelClass::__construct() needs a PDO instance;
 *          // get that instance from the container service
 *          $object = new ModelClass;
 *          $object->setDb($di->get('db'));
 *          return $object;
 *      });
 *      
 *      // it's preferable to use $di->newInstance() instead of new;
 *      // doing so lets you use keyword parameters for construction, and
 *      // merges parent param values as well.
 *      $di->set('another_model', function() use ($di) {
 *          $object = $di->newInstance('ModelClass', array(
 *              'db' => $di->get('db'),
 *          ));
 *          return $object;
 *      });
 *      
 *      // etc, etc, etc
 *      // if you want to define params to use each time a class is
 *      // instantiated, use $di->config.
 *      $di->config['vendor\package\Class'] = array(
 *          'foo' => 'bar',
 *          'zim' => $di->getLazy('service_name');
 *      );
 * 
 * DI is magical and weird if you're not used to it.  Essentially, you pre-
 * define all of your object-creation logic in the container, and then ask the 
 * container for a single object to kick off the process.
 * 
 * @package aura.di
 * 
 */
class Container
{
    /**
     * 
     * A convenient reference to the Config::$external object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $config;
    
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
     * Retains named services.
     * 
     * @var array
     * 
     */
    protected $service = array();
    
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
        // retain for various uses
        $this->forge = $forge;
        
        // convenience property
        $this->config = $this->getForge()->getConfig()->getExternal();
    }
    
    /**
     * 
     * Magic get to provide access to the Forge and the Config::$external 
     * objects.
     * 
     * @param string $key The property to retrieve ('forge' or 'config').
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($key == 'config' || $key == 'forge') {
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
     * Does a particular service exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($key)
    {
        return isset($this->service[$key]);
    }
    
    /**
     * 
     * Sets a service object by name.
     * 
     * If you set a service as a closure, it is automatically treated as a 
     * Lazy.
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object; if a Closure, is treated as a
     * Lazy.
     * 
     */
    public function set($key, $val)
    {
        if (! is_object($val)) {
            throw new Exception_ServiceInvalid($key);
        }
        
        if ($val instanceof \Closure) {
            $val = new Lazy($val);
        }
        
        $this->service[$key] = $val;
    }
    
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
    public function get($key)
    {
        // does the key exist?
        if (! $this->has($key)) {
            throw new Exception_ServiceNotFound($key);
        }
        
        // invoke lazy-loading as needed
        if ($this->service[$key] instanceof Lazy) {
            $this->service[$key] = $this->service[$key]();
        }
        
        // done
        return $this->service[$key];
    }
    
    /**
     * 
     * Gets the list of services provided.
     * 
     * @return array
     * 
     */
    public function getServices()
    {
        return array_keys($this->service);
    }
    
    /**
     * 
     * Returns a Lazy that gets a service. This allows you to replace the
     * following idiom ...
     * 
     *      $di->config['ClassName']['param_name'] = new Lazy(function() use ($di)) {
     *          return $di->get('service');
     *      }
     * 
     * ... with the following:
     * 
     *      $di->config['ClassName']['param_name'] = $di->getLazy('service');
     * 
     * @param string $key The service name; it does not need to exist yet.
     * 
     * @return Lazy A lazy-load object that gets the named service.
     * 
     */
    public function getLazy($key)
    {
        $self = $this;
        return new Lazy(function() use ($self, $key) {
           return $self->get($key); 
        });
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
}
