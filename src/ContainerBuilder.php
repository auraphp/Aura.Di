<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Resolver\AutoResolver;
use Aura\Di\Resolver\Resolver;
use Aura\Di\Resolver\Reflector;

/**
 *
 * Creates and configures a new DI container.
 *
 * @package Aura.Di
 *
 */
class ContainerBuilder
{
    /**
     *
     * Use the auto-resolver.
     *
     * @const true
     *
     */
    const AUTO_RESOLVE = true;

    public function newInstance($autoResolve = false)
    {
        if ($autoResolve) {
            $resolver = new AutoResolver(new Reflector());
        } else {
            $resolver = new Resolver(new Reflector());
        }

        return new Container(new InjectionFactory($resolver));
    }

    /**
     *
     * Creates a new DI container, adds pre-existing service objects, applies
     * ContainerConfig classes to define() services, locks the container, and applies
     * the ContainerConfig instances to modify() services.
     *
     * @param array $services Pre-existing service objects to set into the
     * container.
     *
     * @param array $configClasses A list of ContainerConfig classes to instantiate and
     * invoke for configuring the Container.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Container
     *
     */
    public function newConfiguredInstance(
        array $services = [],
        array $configClasses = [],
        $autoResolve = false
    ) {
        $di = $this->newInstance($autoResolve);

        foreach ($services as $key => $val) {
            $di->set($key, $val);
        }

        $configs = [];
        foreach ($configClasses as $class) {
            $config = $di->newInstance($class);
            $config->define($di);
            $configs[] = $config;
        }

        $di->lock();

        foreach ($configs as $config) {
            $config->modify($di);
        }

        return $di;
    }
}
