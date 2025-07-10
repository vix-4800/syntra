<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

use Exception;

class MissingBinaryException extends Exception
{
    public function __construct(string $binary)
    {
        parent::__construct("'$binary' is not installed.");
    }
}
