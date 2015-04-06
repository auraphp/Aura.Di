<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

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

        return new Container(new Factory($resolver));
    }

    /**
     *
     * Creates a new DI container, adds pre-existing service objects, applies
     * Config classes to define() services, locks the container, and applies
     * the Config instances to modify() services.
     *
     * @param array $services Pre-existing service objects to set into the
     * container.
     *
     * @param array $config_classes A list of Config classes to instantiate and
     * invoke for configuring the container.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Container
     *
     */
    public function newConfiguredInstance(
        array $services = [],
        array $config_classes = [],
        $autoResolve = false
    ) {
        $di = $this->newInstance($autoResolve);

        foreach ($services as $key => $val) {
            $di->set($key, $val);
        }

        $configs = [];
        foreach ($config_classes as $class) {
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
