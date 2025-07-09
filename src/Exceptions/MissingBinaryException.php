<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

class MissingBinaryException extends CommandException
{
    public function __construct(string $binary)
    {
        parent::__construct("'$binary' is not installed.");
    }
}
