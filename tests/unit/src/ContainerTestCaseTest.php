<?php
namespace Aura\Di;

use stdClass;

class ContainerTestCaseTest extends ContainerTestCase
{
    protected function setUp()
    {
        $this->setUpContainer(
            array(),
            array('service' => new stdClass)
        );
    }

    public function testAssertGet()
    {
        $this->assertGet('service', 'stdClass');
    }

    public function testAssertNewInstance()
    {
        $this->assertNewInstance('StdClass');
    }
}
