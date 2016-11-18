<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Aura\Di\Injection\InjectionFactory;

/**
 *
 * Resolves object specifications using the DI container.
 *
 * @package aura/di
 *
 */
class ResolutionHelper
{
    /**
     *
     * The injection factory from the DI container.
     *
     * @var InjectionFactory
     *
     */
    protected $injectionFactory;

    /**
     *
     * Constructor.
     *
     * @param InjectionFactory $injectionFactory The injection factory from the
     * DI container.
     *
     */
    public function __construct(InjectionFactory $injectionFactory)
    {
        $this->injectionFactory = $injectionFactory;
    }

    /**
     *
     * Resolves an object specification.
     *
     * @param mixed $spec The object specification.
     *
     * @return mixed
     *
     */
    public function __invoke($spec)
    {
        if (is_string($spec)) {
            return $this->injectionFactory->newInstance($spec);
        }

        if (is_array($spec) && is_string($spec[0])) {
            $spec[0] = $this->injectionFactory->newInstance($spec[0]);
        }

        return $spec;
    }
}

