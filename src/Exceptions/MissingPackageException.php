<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

class MissingPackageException extends CommandException
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
