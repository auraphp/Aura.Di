<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Interface for dependency injection containers.
 *
 * @package Aura.Di
 *
 */
interface ContainerInterface
{
    /**
     *
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     *
     * @return null
     *
     */
    public function lock();

    /**
     *
     * Is the Container locked?
     *
     * @return bool
     *
     */
    public function isLocked();

    /**
     *
     * Does a particular service exist?
     *
     * @param string $key The service key to look up.
     *
     * @return bool
     *
     */
    public function has($key);

    /**
     *
     * Sets a service object by name.
     *
     * @param string $key The service key.
     *
     * @param object $val The service object.
     *
     */
    public function set($key, $val);

    /**
     *
     * Gets a service object by key, lazy-loading it as needed.
     *
     * @param string $key The service to get.
     *
     * @return object
     *
     * @throws \Aura\Di\Exception\ServiceNotFound when the requested service
     * does not exist.
     *
     */
    public function get($key);

    /**
     *
     * Gets the list of services provided.
     *
     * @return array
     *
     */
    public function getInstances();

    /**
     *
     * Gets the list of service definitions.
     *
     * @return array
     *
     */
    public function getServices();

    /**
     *
     * Returns a Lazy that gets a service.
     *
     * @param string $key The service name; it does not need to exist yet.
     *
     * @return Lazy A lazy-load object that gets the named service.
     *
     */
    public function lazyGet($key);

    /**
     *
     * Returns a new instance of the specified class, optionally
     * with additional override parameters.
     *
     * @param string $class The type of class of instantiate.
     *
     * @param array $params Override parameters for the instance.
     *
     * @param array $setters Override setters for the instance.
     *
     * @return object An instance of the requested class.
     *
     */
    public function newInstance($class, array $params = array(), array $setters = array());

    /**
     *
     * Returns a Lazy that creates a new instance.
     *
     * @param string $class The type of class of instantiate.
     *
     * @param array $params Override parameters for the instance.
     *
     * @param array $setters Override setters for the instance.
     *
     * @return Lazy A lazy-load object that creates the new instance.
     *
     */
    public function lazyNew($class, array $params = array(), array $setters = array());
}
