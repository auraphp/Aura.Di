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

/**
 *
 * An interface for a set of Container configuration instructions.
 *
 * @package Aura.Di
 *
 */
interface ContainerConfigInterface
{
    /**
     *
     * Define params, setters, and services before the Container is locked.
     *
     * @param Container $di The DI container.
     *
     */
    public function define(Container $di): void;

    /**
     *
     * Modify service objects after the Container is locked.
     *
     * @param Container $di The DI container.
     *
     */
    public function modify(Container $di): void;
}
