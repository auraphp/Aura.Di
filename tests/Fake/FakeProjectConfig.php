<?php
namespace Aura\Di\Fake;

use Aura\Di\Config;
use Aura\Di\Container;

class FakeProjectConfig extends Config
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('project_service', (object) ['baz' => 'dib']);
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('project_service')->baz = 'gir';
    }
}
