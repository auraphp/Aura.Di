<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * A set of Container configuration instructions.
 *
 * @package Aura.Di
 *
 */
abstract class Config
{
    /**
     *
     * Define params, setters, and services before the container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function define(Container $di)
    {
    }

    /**
     *
     * Modify service objects after the container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function modify(Container $di)
    {
    }
}
