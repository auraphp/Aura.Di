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
 * A placeholder object to indicate a constructor param is missing.
 *
 * @package Aura.Di
 *
 */
class MissingParam
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
    public function __construct($name)
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
    public function getName()
    {
        return $this->name;
    }
}
