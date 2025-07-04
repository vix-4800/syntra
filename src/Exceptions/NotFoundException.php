<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when a service is not found in the container
 */
class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    //
}
