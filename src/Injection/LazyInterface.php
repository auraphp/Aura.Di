<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

/**
 *
 * This is a Lazy callable.
 *
 * @package Aura.Di
 *
 */
interface LazyInterface
{
    /**
     *
     * Invokes the Lazy to return a result, usually an object.
     *
     * @return mixed
     *
     */
    public function __invoke();
}
