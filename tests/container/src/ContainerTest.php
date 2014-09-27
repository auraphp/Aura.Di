<?php
namespace Aura\Di\_Config;

class ContainerTest extends AbstractContainerTest
{
    protected function getConfigClasses()
    {
        return array(
            'Aura\Di\FakeLibraryConfig',
            'Aura\Di\FakeProjectConfig',
        );
    }

    public function provideGet()
    {
        return array(
            array('library_service', 'StdClass'),
            array('project_service', 'StdClass'),
        );
    }

    public function provideNewInstance()
    {
        return array(
            array('StdClass'),
        );
    }
}
