<?php
namespace Aura\Di;

class FakeLibraryConfig extends Config
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('library_service', (object) array('foo' => 'bar'));
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('library_service')->foo = 'zim';
    }
}
