<?php
namespace Aura\di;

class FakeProjectConfig extends Config
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('project_service', (object) array('baz' => 'dib'));
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('project_service')->baz = 'gir';
    }
}
