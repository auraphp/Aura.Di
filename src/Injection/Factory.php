<?php
declare(strict_types=1);
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

use Aura\Di\Resolver\Blueprint;
use Aura\Di\Resolver\Resolver;

/**
 *
 * A generic factory to create repeated instances of a single class. Note that
 * it does not implement the LazyInterface, so it is not automatically invoked
 * when resolving params and setters.
 *
 * @package Aura.Di
 *
 */
class Factory
{
    /**
     *
     * The Resolver.
     *
     * @var Resolver
     *
     */
    protected $resolver;

    /**
     *
     * Override params for the class.
     *
     * @var Blueprint
     *
     */
    protected $blueprint;

    /**
     *
     * Blueprints that are only used within the context of this factory.
     *
     * @var array|Blueprint[]
     *
     */
    protected $contextualBlueprints = [];

    /**
     *
     * Constructor.
     *
     * @param Resolver $resolver A Resolver to provide class-creation specifics.
     *
     * @param Blueprint $blueprint
     */
    public function __construct(
        Resolver $resolver,
        Blueprint $blueprint
    ) {
        $this->resolver = $resolver;
        $this->blueprint = $blueprint;
    }

    /**
     * @param Blueprint $contextualBlueprint
     * @return Factory
     */
    public function withContext(Blueprint $contextualBlueprint): self
    {
        $clone = clone $this;
        $clone->contextualBlueprints[] = $contextualBlueprint;
        return $clone;
    }

    /**
     *
     * Invoke the Factory object as a function to use the Factory to create
     * a new instance of the specified class; pass sequential parameters as
     * as yet another set of constructor parameter overrides.
     *
     * Why the overrides for the overrides?  So that any package that needs a
     * factory can make its own, using sequential params in a function; then
     * the factory call can be replaced by a call to this Factory.
     *
     * @param array $params
     * @return object
     */
    public function __invoke(...$params): object
    {
        return $this->resolver->resolve(
            $this->blueprint->withParams($params),
            $this->contextualBlueprints
        );
    }
}
