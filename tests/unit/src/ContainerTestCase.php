<?php
namespace Aura\Di;

class ContainerTestCase extends \PHPUnit_Framework_TestCase
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
     * @param array $config_classes Use these config classes.
     *
     * @param array $services Use these predefined service object.
     *
     * @return null
     *
     */
    protected function setUpContainer(
        array $config_classes = array(),
        array $services = array()
    ) {
        $builder = new ContainerBuilder();
        $this->di = $builder->newInstance($services, $config_classes);
    }

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
