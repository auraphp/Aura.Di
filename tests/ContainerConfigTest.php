<?php
namespace Aura\Di\_Config;

class ContainerConfigTest extends AbstractContainerTest
{
    protected function getConfigClasses()
    {
        return array(
            'Aura\Di\FakeLibraryConfig',
            'Aura\Di\FakeProjectConfig',
        );
    }

    protected function getAutoResolve()
    {
        return false;
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
