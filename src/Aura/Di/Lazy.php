<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Wraps a closure specifically for the purpose of lazy-loading an object.
 *
 * @package Aura.Di
 *
 */
class Lazy
{
    /**
     *
     * A closure that creates an object instance.
     *
     * @var \Closure
     *
     */
    protected $closure;

    /**
     *
     * Constructor.
     *
     * @param \Closure $closure A closure that creates an object instance.
     *
     * @return void
     *
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     *
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     *
     */
    public function __invoke()
    {
        $closure = $this->closure;
        return $closure();
    }
}
