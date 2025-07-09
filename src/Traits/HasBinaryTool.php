<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Exceptions\MissingPackageException;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tools\ToolInterface;

trait HasBinaryTool
{
    protected ?string $binary;

    public function findBinaryTool(ToolInterface $tool): void
    {
        $this->binary = find_composer_bin($tool->binaryName(), Project::getRootPath());

        if (!$this->binary) {
            throw new MissingPackageException($tool->packageName(), $tool->installCommand());
        }
    }
}
