<?php
declare(strict_types=1);
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
 * Indicates a Mutation to be invoked after constructing an object
 *
 * @package Aura.Di
 *
 */
interface MutationInterface
{
    /**
     *
     * Invokes the Mutation to return an object.
     *
     * @param object $object
     *
     * @return object
     */
    public function __invoke(object $object): object;
}
