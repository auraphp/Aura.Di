<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

use ReflectionException;

/**
 *
 * A factory to create support objects for the Container.
 *
 * @package Aura.Di
 *
 * @property-read array $params Constructor params for classes.
 *
 * @property-read array $setter Setter definitions for classes/interfaces.
 *
 * @property-read array $values Arbitrary values.
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
        Resolver $resolver,
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new InstanceFactory($resolver, $class, $params, $setter);
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
        Resolver $resolver,
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new LazyNew($resolver, $class, $params, $setter);
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

    /**
     *
     * Returns a new LazyValue.
     *
     * @param string $key The value key to use.
     *
     * @return LazyValue
     *
     */
    public function newLazyValue(Resolver $resolver, $key)
    {
        return new LazyValue($resolver, $key);
    }

}
