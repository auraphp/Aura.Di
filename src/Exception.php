<?php
declare(strict_types=1);
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Psr\Container\ContainerExceptionInterface;

/**
 *
 * Generic package exception.
 *
 * @package Aura.Di
 *
 */
class Exception extends \Exception implements ContainerExceptionInterface
{
    /**
     *
     * The container is locked and connot be modified.
     *
     * @return Exception\ContainerLocked
     *
     */
    static public function containerLocked(): Exception\ContainerLocked
    {
        throw new Exception\ContainerLocked("Cannot modify container when locked.");
    }

    /**
     *
     * A class constructor param was not defined.
     *
     * @param string $class The class name.
     *
     * @param string $param The constructor param name.
     *
     * @return Exception\MissingParam
     *
     */
    static public function missingParam(string $class, string $param): Exception\MissingParam
    {
        throw new Exception\MissingParam("Param missing: {$class}::\${$param}");
    }

    /**
     *
     * The container does not have a requested service.
     *
     * @param string $service The service name.
     *
     * @return Exception\ServiceNotFound
     *
     */
    static public function serviceNotFound(string $service): Exception\ServiceNotFound
    {
        throw new Exception\ServiceNotFound("Service not defined: '{$service}'");
    }

    /**
     *
     * The service was defined as something other than an object.
     *
     * @param string $service The service name.
     *
     * @param mixed $val The service definition.
     *
     * @return Exception\ServiceNotObject
     *
     */
    static public function serviceNotObject(string $service, $val): Exception\ServiceNotObject
    {
        $type = gettype($val);
        $message = "Expected service '{$service}' to be of type 'object', got '{$type}' instead.";
        throw new Exception\ServiceNotObject($message);
    }

    /**
     *
     * A setter method was defined, but it not available on the class.
     *
     * @param string $class The class name.
     *
     * @param string $method The method name.
     *
     * @return Exception\SetterMethodNotFound
     *
     */
    static public function setterMethodNotFound(string $class, string $method): Exception\SetterMethodNotFound
    {
        throw new Exception\SetterMethodNotFound("Setter method not found: {$class}::{$method}()");
    }

    /**
     *
     * A mutation was lazy and returned a value that is not an instanceof MutationInterface.
     *
     * @param mixed $value The returned value.
     *
     * @return Exception\SetterMethodNotFound
     *
     */
    static public function mutationDoesNotImplementInterface($value): Exception\SetterMethodNotFound
    {
        if (\is_object($value)) {
            $className = get_class($value);
            throw new Exception\MutationDoesNotImplementInterface("Mutation does not implement interface: {$className}");
        }

        $typeName = \gettype($value);
        throw new Exception\MutationDoesNotImplementInterface("Expected Mutation interface, got: {$typeName}");
    }

    /**
     *
     * A requested property does not exist.
     *
     * @param string $name The property name.
     *
     * @return Exception\NoSuchProperty
     *
     */
    static public function noSuchProperty(string $name): Exception\NoSuchProperty
    {
        throw new Exception\NoSuchProperty("Property does not exist: \${$name}");
    }
}
