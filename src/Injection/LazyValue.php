<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

use Aura\Di\Resolver\Resolver;

/**
 *
 * Returns an arbitrary value when invoked.
 *
 * @package Aura.Di
 *
 */
class LazyValue implements LazyInterface
{
    /**
     *
     * The Resolver that holds the values.
     *
     * @var Resolver
     *
     */
    protected $resolver;

    /**
     *
     * The value key to retrieve.
     *
     * @var string
     *
     */
    protected $key;

    /**
     *
     * Constructor.
     *
     * @param Resolver $resolver The Resolver that holds the values.
     *
     * @param string $key The value key to retrieve.
     *
     */
    public function __construct(Resolver $resolver, string $key)
    {
        $this->resolver = $resolver;
        $this->key = $key;
    }

    /**
     *
     * Returns the lazy value.
     *
     * @return mixed
     *
     */
    public function __invoke()
    {
        if (!isset($this->resolver->values[$this->key])) {
            throw new \InvalidArgumentException('Unknown key (' . $this->key . ') in container value');
        }

        $value = $this->resolver->values[$this->key];
        // convert Lazy objects
        if ($value instanceof LazyInterface) {
            $value = $value();
        }
        return $value;
    }
}
