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

/**
 *
 * Use this trait in \PHPUnit_Framework_TestCase classes to test
 * Container service and new instance configurations. The Container
 * must be in the $this->di property.
 *
 * @package Aura.Di
 *
 */
trait ContainerAssertionsTrait
{
    /**
     *
     * Asserts the container gets a particular class for a named
     * service.
     *
     * @param string $name The service name.
     *
     * @param string $class The expected class.
     *
     * @return null
     *
     */
    protected function assertGet($name, $class)
    {
        $this->assertInstanceOf(
            $class,
            $this->di->get($name)
        );
    }

    /**
     *
     * Asserts the Container creates a new instance of a class.
     *
     * @param string $class The class to create.
     *
     * @param array $params An array of override params.
     *
     * @param array $setter An array of overrider setter methods.
     *
     * @return null
     *
     */
    protected function assertNewInstance(
        $class,
        $params = array(),
        $setter = array()
    ) {
        $this->assertInstanceOf(
            $class,
            $this->di->newInstance($class, $params, $setter)
        );
    }
}
