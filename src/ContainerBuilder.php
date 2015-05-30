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
        $resolver = $this->newResolver($autoResolve);
        return new Container(new InjectionFactory($resolver));
    }

    protected function newResolver($autoResolve = false)
    {
        if ($autoResolve) {
            return new AutoResolver(new Reflector());
        }

        return new Resolver(new Reflector());
    }

    /**
     *
     * Creates a new DI container, applies ContainerConfig classes to define()
     * services, locks the container, and applies the ContainerConfig instances
     * to modify() services.
     *
     * @param array $configClasses A list of ContainerConfig classes to
     * instantiate and invoke for configuring the Container.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Container
     *
     */
    public function newConfiguredInstance(
        array $configClasses = [],
        $autoResolve = false
    ) {
        $di = $this->newInstance($autoResolve);

        $configs = [];
        foreach ($configClasses as $configClass) {
            $config = $di->newInstance($configClass);
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
