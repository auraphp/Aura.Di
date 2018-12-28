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
 * A placeholder object to indicate a constructor param is missing.
 *
 * @package Aura.Di
 *
 */
class UnresolvedParam
{
    /**
     *
     * The name of the missing param.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * Constructor.
     *
     * @param string $name The name of the missing param.
     *
     */
    public function __construct(string $name)
    {
        $this->name = $name;
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
}
