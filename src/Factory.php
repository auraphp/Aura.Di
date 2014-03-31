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
 * A factory to create support objects for the Container.
 * 
 * @package Aura.Di
 * 
 */
class Factory
{
    /**
     * 
     * Returns a new InstanceFactory.
     * 
     * @param Container $container The service container.
     * 
     * @param string $class The class to create.
     * 
     * @param array $params Override params for the class.
     * 
     * @param array $setter Override setters for the class.
     * 
     * @return InstanceFactory
     * 
     */
    public function newInstanceFactory(
        Container $container,
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new InstanceFactory($container, $class, $params, $setter);
    }
    
    /**
     * 
     * Returns a new Lazy.
     * 
     * @param callable $callable The callable to invoke.
     * 
     * @param array $params Arguments for the callable.
     * 
     * @return Lazy
     * 
     */
    public function newLazy($callable, array $params = array())
    {
        return new Lazy($callable, $params);
    }
    
    /**
     * 
     * Returns a new LazyGet.
     * 
     * @param Container $container The service container.
     * 
     * @param string $service The service to retrieve.
     * 
     * @return LazyGet
     * 
     */
    public function newLazyGet(Container $container, $service)
    {
        return new LazyGet($container, $service);
    }
    
    /**
     * 
     * Returns a new LazyInclude.
     * 
     * @param string $file The file to include.
     * 
     * @return LazyInclude
     * 
     */
    public function newLazyInclude($file)
    {
        return new LazyInclude($file);
    }
    
    /**
     * 
     * Returns a new LazyNew.
     * 
     * @param Container $container The service container.
     * 
     * @param string $class The class to instantiate.
     * 
     * @param array $params Params for the instantiation.
     * 
     * @param array $setter Setters for the instantiation.
     * 
     * @return Lazy
     * 
     */
    public function newLazyNew(
        Container $container,
        $class,
        array $params,
        array $setter
    ) {
        return new LazyNew($container, $class, $params, $setter);
    }
    
    /**
     * 
     * Returns a new LazyRequire.
     * 
     * @param string $file The file to require.
     * 
     * @return LazyRequire
     * 
     */
    public function newLazyRequire($file)
    {
        return new LazyRequire($file);
    }
}
