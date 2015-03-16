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
