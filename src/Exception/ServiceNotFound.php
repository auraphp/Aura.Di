<?php
declare(strict_types=1);
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Exception;

use Aura\Di\Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 *
 * The named service was not found.
 *
 * @package Aura.Di
 *
 */
class ServiceNotFound extends Exception implements NotFoundExceptionInterface
{
}
