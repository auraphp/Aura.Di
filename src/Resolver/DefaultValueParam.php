<?php
declare(strict_types=1);
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

/**
 *
 * A placeholder object to indicate a constructor param is using a default value.
 *
 * @package Aura.Di
 *
 */
class DefaultValueParam
{
    /**
     *
     * The name of the param.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * The default value of the param.
     *
     * @var mixed
     *
     */
    protected $value;

    /**
     *
     * Constructor.
     *
     * @param string $name The name of the param.
     *
     * @param mixed $value The default value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     *
     * Returns the name of the missing param.
     *
     * @return string
     *
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * Returns the default value.
     *
     * @return mixed
     *
     */
    public function getValue()
    {
        return $this->value;
    }
}
