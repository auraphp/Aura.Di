<?php
namespace Aura\Di;

class ContainerAssertionsTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAssertionsTrait;

    protected $di;

    protected function setUp()
    {
        parent::setUp();
        $this->di = new Container(new Factory);
    }

    public function testAssertGet()
    {
        $this->di->set('service', $this->di->lazyNew('StdClass'));
        $this->assertGet('service', 'StdClass');
    }

    public function testAssertNewInstance()
    {
        $this->assertNewInstance('StdClass');
    }
}
