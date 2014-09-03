<?php
namespace Aura\Di;

use StdClass;

class ContainerAssertionsTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAssertionsTrait;

    protected function setUp()
    {
        $this->setUpContainer(
            array(),
            array('service' => new StdClass)
        );
    }

    public function testAssertGet()
    {
        $this->assertGet('service', 'StdClass');
    }

    public function testAssertNewInstance()
    {
        $this->assertNewInstance('StdClass');
    }
}
