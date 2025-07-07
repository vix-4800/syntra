<?php

declare(strict_types=1);

namespace Vix\Syntra\Exceptions;

class MissingBinaryException extends CommandException
{
/**
 * Class constructor.
 */
    public function __construct(string $binary, public ?string $suggestedInstall = null)
    {
        $message = "'$binary' is not installed.";

        if ($suggestedInstall) {
            $message .= "\nTo install: $suggestedInstall";
        }

        parent::__construct($message);
    }
}
