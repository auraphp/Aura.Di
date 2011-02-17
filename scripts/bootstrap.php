<?php
use aura\di\Config as Config;
use aura\di\Container as Container;
use aura\di\Forge as Forge;
require dirname(__DIR__) . "/src/ConfigInterface.php";
require dirname(__DIR__) . "/src/Config.php";
require dirname(__DIR__) . "/src/Container.php";
require dirname(__DIR__) . "/src/Exception.php";
require dirname(__DIR__) . "/src/Exception/ServiceInvalid.php";
require dirname(__DIR__) . "/src/Exception/ServiceNotFound.php";
require dirname(__DIR__) . "/src/ForgeInterface.php";
require dirname(__DIR__) . "/src/Forge.php";
require dirname(__DIR__) . "/src/Lazy.php";
return new Container(new Forge(new Config(new \ArrayObject)));
