<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

class RectorTool extends AbstractTool
{
    public function __construct()
    {
        parent::__construct('rector', 'rector/rector', 'Rector (code refactoring)');
    }
}
