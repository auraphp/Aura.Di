<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

use Aura\Di\LazyNew;
use ReflectionParameter;

/**
 *
 * This extension of the Resolver additionally auto-resolves unspecified
 * constructor params according to their typehints; use with caution as it can
 * be very difficult to debug.
 *
 * @package Aura.Di
 *
 */
class AutoResolver extends Resolver
{
    /**
     *
     * Auto-resolve these typehints to these values.
     *
     * @var array
     *
     */
    protected $types = [];

    /**
     *
     * Auto-resolves a unified param.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The auto-resolved param value.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, $class, $parent)
    {
        $unified = parent::getUnifiedParam($rparam, $class, $parent);
        if (! $unified instanceof ParamPlaceholder) {
            return $unified;
        }

        if ($rparam->isArray()) {
            // use an empty array
            return [];
        }

        $rtype = $rparam->getClass();
        if ($rtype && isset($this->types[$rtype->name])) {
            // use an explicit auto-resolution
            return $this->types[$rtype->name];
        }

        if ($rtype && $rtype->isInstantiable()) {
            // use a lazy-new-instance of the typehinted class
            return new LazyNew($this, $rtype->name);
        }

        // use a null as a placeholder
        return null;
    }
}
