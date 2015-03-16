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
 * Wraps a callable specifically for the purpose of lazy-loading an object.
 *
 * @package Aura.Di
 *
 */
class LazyInclude implements LazyInterface
{
    /**
     *
     * The file to include.
     *
     * @var string
     *
     */
    protected $file;

    /**
     *
     * Constructor.
     *
     * @param string $file The file to include.
     *
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     *
     * Invokes the closure to include the file.
     *
     * @return mixed The return from the included file, if any.
     *
     */
    public function __invoke()
    {
        return include $this->file;
    }
}
