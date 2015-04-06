<?php
namespace Aura\Di;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testNewInstance()
    {
        $builder = new ContainerBuilder();

        $preset_service = (object) ['irk' => 'doom'];
        $services = [
            'preset_service' => $preset_service
        ];

        $config_classes = [
            'Aura\Di\FakeLibraryConfig',
            'Aura\Di\FakeProjectConfig',
        ];

        $di = $builder->newConfiguredInstance($services, $config_classes);

        $this->assertInstanceOf('Aura\Di\Container', $di);
        $this->assertSame($preset_service, $di->get('preset_service'));

        $expect = 'zim';
        $actual = $di->get('library_service');
        $this->assertSame($expect, $actual->foo);

        $expect = 'gir';
        $actual = $di->get('project_service');
        $this->assertSame($expect, $actual->baz);
    }
}
