<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tools\ToolInterface;

trait FindsToolBinaryTrait
{
    protected function findToolBinary(ToolInterface $tool): string
    {
        $binary = find_composer_bin($tool->binaryName(), Project::getRootPath());

        if (!$binary) {
            throw new MissingBinaryException($tool->binaryName(), $tool->installCommand());
        }

        return $binary;
    }
}
