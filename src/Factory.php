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
 */
class Factory
{
    protected $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    public function newInstance(
        $class,
        array $merge_params = array(),
        array $merge_setter = array()
    ) {
        return $this->resolver->newInstance($class, $merge_params, $merge_setter);
    }

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
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new InstanceFactory($this->resolver, $class, $params, $setter);
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
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        return new LazyNew($this->resolver, $class, $params, $setter);
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
    public function newLazyValue($key)
    {
        return new LazyValue($this->resolver, $key);
    }

}
