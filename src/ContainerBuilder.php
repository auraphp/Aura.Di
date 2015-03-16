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
 * Creates and configures a new DI container.
 *
 * @package Aura.Di
 *
 */
class ContainerBuilder
{
    /**
     *
     * Enable auto-resolution after the define() step.
     *
     * @const true
     *
     */
    const ENABLE_AUTO_RESOLVE = true;

    /**
     *
     * Disable auto-resolution after the define() step.
     *
     * @const true
     *
     */
    const DISABLE_AUTO_RESOLVE = false;

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
     * @param bool $auto_resolve Enable or disable auto-resolve after the
     * define() step?
     *
     * @return Container
     *
     */
    public function newInstance(
        array $services = array(),
        array $config_classes = array(),
        $auto_resolve = self::ENABLE_AUTO_RESOLVE
    ) {
        $di = new Container(new Factory);
        $di->setAutoResolve($auto_resolve);

        foreach ($services as $key => $val) {
            $di->set($key, $val);
        }

        $configs = array();
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
