<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

use Exception;

class DirectoryNotFoundException extends Exception
{
    public function __construct(string $dir)
    {
        parent::__construct("Directory '$dir' does not exist.");
    }
}
