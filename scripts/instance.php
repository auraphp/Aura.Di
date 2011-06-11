<?php
namespace Aura\Di;
require_once dirname(__DIR__) . '/src.php';
return new Manager(new Forge(new Config));
