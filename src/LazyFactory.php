<?php
namespace Aura\Di;

class LazyFactory
{
    public function newLazy($callable, array $params)
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
    
    public function newLazyInstance(
        Container $container,
        $class,
        array $params,
        array $setter
    ) {
        return new LazyInstance($container, $class, $params, $setter);
    }
    
    public function newLazyRequire($file)
    {
        return new LazyRequire($file);
    }
}
