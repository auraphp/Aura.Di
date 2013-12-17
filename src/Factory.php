<?php
namespace Aura\Di;

use Aura\Di\Container;

class Factory
{
    public function newInstanceFactory(
        Container $container,
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new InstanceFactory($container, $class, $params, $setter);
    }
    
    public function newLazy($callable, array $params = array())
    {
        return new Lazy($callable, $params);
    }
    
    public function newLazyGet(Container $container, $service)
    {
        return new LazyGet($container, $service);
    }
    
    public function newLazyInclude($file)
    {
        return new LazyInclude($file);
    }
    
    public function newLazyNew(
        Container $container,
        $class,
        array $params,
        array $setter
    ) {
        return new LazyNew($container, $class, $params, $setter);
    }
    
    public function newLazyRequire($file)
    {
        return new LazyRequire($file);
    }
}
