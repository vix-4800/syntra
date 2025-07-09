<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

use Exception;

class MissingPackageException extends Exception
{
    public function __construct(string $package, public ?string $suggestedInstall = null)
    {
        $message = "'$package' package is not installed.";

        if ($suggestedInstall) {
            $message .= "\nTo install: $suggestedInstall";
        }

        parent::__construct($message);
    }
}
