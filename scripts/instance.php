<?php
namespace aura\di;
require_once dirname(__DIR__) . '/src.php';
return new Manager(new Forge(new Config));
