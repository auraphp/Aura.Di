<?php
namespace Aura\Di\Fake;

use Aura\Di\Config;
use Aura\Di\Container;

class FakeLibraryConfig extends Config
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('library_service', (object) ['foo' => 'bar']);
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('library_service')->foo = 'zim';
    }
}
