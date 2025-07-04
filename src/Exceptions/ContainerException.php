<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when the container encounters an error
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    //
}
