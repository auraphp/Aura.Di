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
    protected $param;

    /**
     *
     * Constructor.
     *
     * @param string $name The name of the missing param.
     *
     */
    public function __construct($class, $param)
    {
        $this->class = $class;
        $this->param = $param;
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
        return "{$this->class}::\${$this->param}";
    }
}
