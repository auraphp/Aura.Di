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

use Aura\Di\Injection\LazyNew;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 *
 * This extension of the Resolver additionally auto-resolves unspecified
 * constructor params according to their typehints; use with caution as it can
 * be very difficult to debug.
 *
 * @package Aura.Di
 *
 * @property array $types
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
     * Auto-resolves params typehinted to classes.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The auto-resolved param value, or UnresolvedParam.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, string $class, array $parent)
    {
        $unified = parent::getUnifiedParam($rparam, $class, $parent);

        // already resolved?
        if (! $unified instanceof UnresolvedParam && ! $unified instanceof DefaultValueParam) {
            return $unified;
        }

        try {
            $rtype = $rparam->getType()
                ? new ReflectionClass($rparam->getType()->getName())
                : null ;
        } catch (ReflectionException $re) {
            if (0 === substr_compare(
                $re->getMessage(),
                'does not exist',
                -\strlen('does not exist')
            )
            ) {
                $rtype = null;
            } else {
                throw $re;
            }
        }

        if ($rtype && isset($this->types[$rtype->name])) {
            return $this->types[$rtype->name];
        }

        if ($unified instanceof DefaultValueParam) {
            return $unified;
        }

        // use a lazy-new-instance of the typehinted class?
        if ($rtype && $rtype->isInstantiable()) {
            return new LazyNew($this, new Blueprint($rtype->name));
        }

        // $unified is still an UnresolvedParam
        return $unified;
    }
}
