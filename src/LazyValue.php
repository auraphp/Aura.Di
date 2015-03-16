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
 * Lazy-loads an arbitrary value.
 *
 * @package Aura.Di
 *
 */
class LazyValue implements LazyInterface
{
    /**
     *
     * The values.
     *
     * @var array
     *
     */
    protected $values;

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
     * @param array $values The arbitrary values.
     *
     * @param string $key The value key to retrieve.
     *
     */
    public function __construct(array &$values, $key)
    {
        $this->values =& $values;
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
        return $this->values[$this->key];
    }
}
