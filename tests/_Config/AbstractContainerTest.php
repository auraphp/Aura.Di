<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di\_Config;

use Aura\Di\ContainerBuilder;

/**
 *
 * Use this extension of \PHPUnit_Framework_TestCase classes to test
 * configuration of services and new instances through a Container.
 *
 * @package Aura.Di
 *
 */
abstract class AbstractContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * The Container.
     *
     * @var Container
     *
     */
    protected $di;

    /**
     *
     * Sets a new Container into $this->di.
     *
     * @return null
     *
     */
    protected function setUp()
    {
        $builder = new ContainerBuilder;
        $this->di = $builder->newInstance(
            $this->getServices(),
            $this->getConfigClasses(),
            $this->getAutoResolve()
        );
    }

    /**
     *
     * Returns predefined service objects to pass to the ContainerBuilder.
     *
     * @return array
     *
     */
    protected function getServices()
    {
        return array();
    }

    /**
     *
     * Returns Config classes to pass to the ContainerBuilder.
     *
     * @return array
     *
     */
    protected function getConfigClasses()
    {
        return array();
    }

    /**
     *
     * Should auto-resolution be enabled?
     *
     * @return bool
     *
     */
    protected function getAutoResolve()
    {
        return true;
    }

    /**
     *
     * Tests that a service is of the expected class.
     *
     * @param string $name The service name.
     *
     * @param string $class The expected class.
     *
     * @return null
     *
     * @dataProvider provideGet
     *
     */
    public function testGet($name, $class)
    {
        if (! $name) {
            $this->markTestSkipped('No service name passed for testGet().');
        }

        $this->assertInstanceOf(
            $class,
            $this->di->get($name)
        );
    }

    /**
     *
     * Provides data for testGet().
     *
     * @return array An array of sequential elements, where each element is
     * itself an array('service_name', 'ExpectedClassName').
     *
     */
    public function provideGet()
    {
        return array(array(null, null));
    }

    /**
     *
     * Tests that a class can be instantiated through the Container.
     *
     * @param string $class The expected class.
     *
     * @return null
     *
     * @dataProvider provideNewInstance
     *
     */
    public function testNewInstance(
        $class,
        array $params = array(),
        array $setter = array()
    ) {
        if (! $class) {
            $this->markTestSkipped('No class name passed for testNewInstance().');
        }

        $this->assertInstanceOf(
            $class,
            $this->di->newInstance($class, $params, $setter)
        );
    }

    /**
     *
     * Provides data for testNewInstance().
     *
     * @return array An array of sequential elements, where each element is
     * itself an array('ClassName', array(param, param, param),
     * array(setter, setter, setter)).
     *
     */
    public function provideNewInstance()
    {
        return array(array(null));
    }
}
