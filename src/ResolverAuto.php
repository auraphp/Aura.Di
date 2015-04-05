<?php
namespace Aura\Di;

use ReflectionParameter;

class ResolverAuto extends Resolver
{
    /**
     *
     * Auto-resolve these typehints to these values.
     *
     * @var array
     *
     */
    protected $types = array();

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
     * @param string $name The param name.
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
            return array();
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
