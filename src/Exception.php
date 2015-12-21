<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Interop\Container\Exception\ContainerException;

/**
 *
 * Generic package exception.
 *
 * @package Aura.Di
 *
 */
class Exception extends \Exception implements ContainerException
{
    static public function containerLocked()
    {
        throw new Exception\ContainerLocked("Cannot modify container when locked.");
    }

    static public function containerNotLocked()
    {
        throw new Exception\ContainerNotLocked("Container must be locked first.");
    }

    static public function missingParam($class, $param)
    {
        throw new Exception\MissingParam("Param missing: {$class}::\${$param}");
    }

    static public function serviceNotFound($service)
    {
        throw new Exception\ServiceNotFound("Service not defined: '{$service}'");
    }

    static public function serviceNotObject($service, $val)
    {
        $type = gettype($val);
        $message = "Expected service '{$service}' to be of type 'object', got '{$type}' instead.";
        throw new Exception\ServiceNotObject($message);
    }

    static public function serviceMethodNotFound()
    {
        throw new Exception\ServiceMethodNotFound();
    }

    static public function setterMethodNotFound($class, $method)
    {
        throw new Exception\SetterMethodNotFound("Setter method not found: {$class}::{$method}()");
    }

    static public function noSuchProperty($name)
    {
        throw new Exception\NoSuchProperty("Property does not exist: \${$name}");
    }
}
