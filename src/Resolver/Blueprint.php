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
use Aura\Di\Injection\LazyInterface;
use Aura\Di\Injection\MutationInterface;
use ReflectionClass;
use function array_values;

final class Blueprint
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $setters;

    /**
     * @var array
     */
    private $mutations;

    /**
     * @param string $className
     * @param array $params
     * @param array $setters
     * @param array $mutations
     */
    public function __construct(
        string $className,
        array $params = [],
        array $setters = [],
        array $mutations = []
    )
    {
        $this->className = $className;
        $this->params = $params;
        $this->setters = $setters;
        $this->mutations = $mutations;
    }

    /**
     * Merges all parameters and invokes the lazy ones.
     *
     * @param Blueprint $mergeBlueprint The overrides during merging
     *
     * @return Blueprint The merged blueprint
     */
    public function merge(Blueprint $mergeBlueprint): Blueprint
    {
        return new Blueprint(
            $this->className,
            $this->mergeParams($mergeBlueprint),
            $this->mergeSetters($mergeBlueprint),
            $this->mergeMutations($mergeBlueprint)
        );
    }

    /**
     * Instantiates a new object based on the current blueprint.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return object
     */
    public function __invoke(ReflectionClass $reflectedClass): object
    {
        $object = $reflectedClass->newInstanceArgs(
            array_map(
                function ($val) {
                    // is the param missing?
                    if ($val instanceof UnresolvedParam) {
                        throw Exception::missingParam($this->className, $val->getName());
                    }

                    // load lazy objects as we go
                    if ($val instanceof LazyInterface) {
                        $val = $val();
                    }

                    return $val;
                },
                array_values($this->params)
            )
        );

        foreach ($this->setters as $method => $value) {
            if (! method_exists($this->className, $method)) {
                throw Exception::setterMethodNotFound($this->className, $method);
            }
            if ($value instanceof LazyInterface) {
                $value = $value();
            }
            $object->$method($value);
        }

        /** @var MutationInterface $mutation */
        foreach ($this->mutations as $mutation) {
            if ($mutation instanceof LazyInterface) {
                $mutation = $mutation();
            }

            if ($mutation instanceof MutationInterface === false) {
                throw Exception::mutationDoesNotImplementInterface($mutation);
            }

            $object = $mutation($object);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getSetters(): array
    {
        return $this->setters;
    }

    /**
     * @return array
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }

    /**
     * @param array $params
     * @return Blueprint
     */
    public function replaceParams(array $params): self
    {
        $clone = clone $this;
        $clone->params = $params;
        return $clone;
    }

    /**
     * @param array $params
     * @return Blueprint
     */
    public function withParams(array $params): self
    {
        $clone = clone $this;
        $clone->params = \array_merge($this->params, $params);
        return $clone;
    }

    /**
     *
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param Blueprint $mergeBlueprint A blueprint containing override setters.
     *
     * @return array The merged setters
     */
    private function mergeSetters(Blueprint $mergeBlueprint): array
    {
        return array_merge($this->setters, $mergeBlueprint->setters);
    }

    /**
     *
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param Blueprint $mergeBlueprint A blueprint containing additional mutations.
     *
     * @return array The merged mutations
     */
    private function mergeMutations(Blueprint $mergeBlueprint): array
    {
        return array_merge($this->mutations, $mergeBlueprint->mutations);
    }

    /**
     *
     * Merges the params with overides; also invokes Lazy values.
     *
     * @param Blueprint $mergeBlueprint A blueprint containing override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     *
     * @return array The merged params
     *
     */
    private function mergeParams(Blueprint $mergeBlueprint): array
    {
        if (! $mergeBlueprint->params) {
            // no params to merge, micro-optimize the loop
            return $this->params;
        }

        $params = $this->params;

        $pos = 0;
        foreach ($params as $key => $val) {

            // positional overrides take precedence over named overrides
            if (array_key_exists($pos, $mergeBlueprint->params)) {
                // positional override
                $val = $mergeBlueprint->params[$pos];
            } elseif (array_key_exists($key, $mergeBlueprint->params)) {
                // named override
                $val = $mergeBlueprint->params[$key];
            }

            // retain the merged value
            $params[$key] = $val;

            // next position
            $pos += 1;
        }

        return $params;
    }
}