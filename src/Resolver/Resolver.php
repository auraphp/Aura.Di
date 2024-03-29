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

use Aura\Di\Exception;
use ReflectionParameter;
use function class_exists;
use function get_parent_class;

/**
 *
 * Resolves class creation specifics based on constructor params and setter
 * definitions, unified across class defaults, inheritance hierarchies, and
 * configuration.
 *
 * @package Aura.Di
 *
 * @property array $params
 *
 * @property array $setters
 *
 * @property array $mutations
 *
 * @property array $types
 *
 * @property array $values
 *
 */
class Resolver
{
    /**
     *
     * A Reflector.
     *
     * @var Reflector
     *
     */
    protected $reflector;

    /**
     *
     * Constructor params in the form `$params[$class][$name] = $value`.
     *
     * @var array
     *
     */
    protected $params = [];

    /**
     *
     * Setter definitions in the form of `$setters[$class][$method] = $value`.
     *
     * @var array
     *
     */
    protected $setters = [];

    /**
     *
     * Setter definitions in the form of `$mutations[$class][] = $value`.
     *
     * @var array
     *
     */
    protected $mutations = [];

    /**
     *
     * Arbitrary values in the form of `$values[$key] = $value`.
     *
     * @var array
     *
     */
    protected $values = [];

    /**
     *
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and configuration.
     *
     * @var array|Blueprint[]
     *
     */
    protected $unified = [];

    /**
     *
     * Constructor.
     *
     * @param Reflector $reflector A collection point for Reflection data.
     *
     */
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     *
     * Returns a reference to various property arrays.
     *
     * @param string $key The property name to return.
     *
     * @return array
     *
     * @throws Exception\NoSuchProperty
     *
     */
    public function &__get($key): array
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
        throw Exception::noSuchProperty($key);
    }

    /**
     *
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overrides, invoking Lazy
     * values along the way.
     *
     * @param Blueprint $blueprint The blueprint to be resolved containing
     * its overrides for this specific case.
     *
     * @param array $contextualBlueprints
     *
     * @return object
     */
    public function resolve(Blueprint $blueprint, array $contextualBlueprints = []): object
    {
        if ($contextualBlueprints === []) {
            return call_user_func(
                $this->expandParams($this->getUnified($blueprint->getClassName())->merge($blueprint)),
                $this->reflector->getClass($blueprint->getClassName())
            );
        }

        $remember = new self($this->reflector);

        foreach ($contextualBlueprints as $contextualBlueprint) {
            $className = $contextualBlueprint->getClassName();

            $remember->params[$className] = $this->params[$className] ?? [];
            $remember->setters[$className] = $this->setters[$className] ?? [];
            $remember->mutations[$className] = $this->mutations[$className] ?? [];

            $this->params[$className] = \array_merge(
                $this->params[$className] ?? [],
                $contextualBlueprint->getParams()
            );

            $this->setters[$className] = \array_merge(
                $this->setters[$className] ?? [],
                $contextualBlueprint->getSetters()
            );

            $this->setters[$className] = \array_merge(
                $this->setters[$className] ?? [],
                $contextualBlueprint->getMutations()
            );

            unset($this->unified[$className]);
        }

        $resolved = call_user_func(
            $this->expandParams($this->getUnified($blueprint->getClassName())->merge($blueprint)),
            $this->reflector->getClass($blueprint->getClassName())
        );

        foreach ($contextualBlueprints as $contextualBlueprint) {
            $className = $contextualBlueprint->getClassName();
            $this->params[$className] = $remember->params[$className] ?? [];
            $this->setters[$className] = $remember->setters[$className] ?? [];
            $this->mutations[$className] = $remember->mutations[$className] ?? [];

            if (isset($remember->unified[$className])) {
                $this->unified[$className] = $remember->unified[$className];
            } else {
                unset($this->unified[$className]);
            }
        }

        return $resolved;
    }

    /**
     *
     * Returns the unified constructor params and setters for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @return Blueprint A blueprint how to construct an object
     *
     */
    public function getUnified(string $class): Blueprint
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // fetch the values for parents so we can inherit them
        $parent = class_exists($class) ? get_parent_class($class) : null;
        if ($parent) {
            $spec = $this->getUnified($parent);
        } else {
            $spec = new Blueprint($class);
        }

        // stores the unified params and setters
        $this->unified[$class] = new Blueprint(
            $class,
            $this->getUnifiedParams($class, $spec->getParams()),
            $this->getUnifiedSetters($class, $spec->getSetters()),
            $this->getUnifiedMutations($class, $spec->getMutations())
        );

        // done, return the unified values
        return $this->unified[$class];
    }

    /**
     *
     * Returns the unified constructor params for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return array The unified params.
     *
     */
    protected function getUnifiedParams(string $class, array $parent): array
    {
        // reflect on what params to pass, in which order
        $unified = [];
        $rparams = $this->reflector->getParams($class);
        foreach ($rparams as $rparam) {
            $unified[$rparam->name] = $this->getUnifiedParam(
                $rparam,
                $class,
                $parent
            );
        }

        return $unified;
    }

    /**
     *
     * Returns a unified param.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The unified param value.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, string $class, array $parent)
    {
        $name = $rparam->getName();
        $pos = $rparam->getPosition();

        // is there a positional value explicitly from the current class?
        $explicitPos = isset($this->params[$class])
                 && array_key_exists($pos, $this->params[$class])
                 && ! $this->params[$class][$pos] instanceof UnresolvedParam;
        if ($explicitPos) {
            return $this->params[$class][$pos];
        }

        // is there a named value explicitly from the current class?
        $explicitNamed = isset($this->params[$class])
                 && array_key_exists($name, $this->params[$class])
                 && ! $this->params[$class][$name] instanceof UnresolvedParam;
        if ($explicitNamed) {
            return $this->params[$class][$name];
        }

        // is there a named value implicitly inherited from the parent class?
        // (there cannot be a positional parent. this is because the unified
        // values are stored by name, not position.)
        $implicitNamed = array_key_exists($name, $parent)
                 && ! $parent[$name] instanceof UnresolvedParam
                 && ! $parent[$name] instanceof DefaultValueParam;
        if ($implicitNamed) {
            return $parent[$name];
        }

        // is a default value available for the current class?
        if ($rparam->isDefaultValueAvailable()) {
            return new DefaultValueParam($name, $rparam->getDefaultValue());
        }

        // is a default value available for the parent class?
        $parentDefault = array_key_exists($name, $parent)
            && $parent[$name] instanceof DefaultValueParam;
        if ($parentDefault) {
            return $parent[$name];
        }

        // param is missing
        return new UnresolvedParam($name);
    }

    /**
     *
     * Returns the unified mutations for a class.
     *
     * Class-specific mutations are executed last before trait-based mutations and before interface-based mutations.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified setters.
     *
     * @return array The unified mutations.
     *
     */
    protected function getUnifiedMutations(string $class, array $parent): array
    {
        $unified = $parent;

        // look for interface mutations
        $interfaces = class_implements($class);
        foreach ($interfaces as $interface) {
            if (isset($this->mutations[$interface])) {
                $unified = array_merge(
                    $this->mutations[$interface],
                    $unified
                );
            }
        }

        // look for trait mutations
        $traits = $this->reflector->getTraits($class);
        foreach ($traits as $trait) {
            if (isset($this->mutations[$trait])) {
                $unified = array_merge(
                    $this->mutations[$trait],
                    $unified
                );
            }
        }

        // look for class mutations
        if (isset($this->mutations[$class])) {
            $unified = array_merge(
                $unified,
                $this->mutations[$class]
            );
        }

        return $unified;
    }

    /**
     *
     * Returns the unified setters for a class.
     *
     * Class-specific setters take precendence over trait-based setters, which
     * take precedence over interface-based setters.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified setters.
     *
     * @return array The unified setters.
     *
     */
    protected function getUnifiedSetters(string $class, array $parent): array
    {
        $unified = $parent;

        // look for interface setters
        $interfaces = class_implements($class);
        foreach ($interfaces as $interface) {
            if (isset($this->setters[$interface])) {
                $unified = array_merge(
                    $this->setters[$interface],
                    $unified
                );
            }
        }

        // look for trait setters
        $traits = $this->reflector->getTraits($class);
        foreach ($traits as $trait) {
            if (isset($this->setters[$trait])) {
                $unified = array_merge(
                    $this->setters[$trait],
                    $unified
                );
            }
        }

        // look for class setters
        if (isset($this->setters[$class])) {
            $unified = array_merge(
                $unified,
                $this->setters[$class]
            );
        }

        return $unified;
    }

    /**
     * Expands variadic parameters onto the end of a contructor parameters array.
     *
     * @param Blueprint $blueprint The blueprint to expand parameters for.
     *
     * @return Blueprint The blueprint with expanded constructor parameters.
     */
    protected function expandParams(Blueprint $blueprint): Blueprint
    {
        $class = $blueprint->getClassName();
        $params = $blueprint->getParams();

        $variadicParams = [];
        foreach ($this->reflector->getParams($class) as $reflectParam) {
            $paramName = $reflectParam->getName();
            if ($reflectParam->isVariadic() && is_array($params[$paramName])) {
                $variadicParams = array_merge($variadicParams, $params[$paramName]);
                unset($params[$paramName]);
                break; // There can only be one
            }

            if ($params[$paramName] instanceof DefaultValueParam) {
                $params[$paramName] = $params[$paramName]->getValue();
            }
        }

        return $blueprint->replaceParams(array_merge($params, array_values($variadicParams)));
    }
}
