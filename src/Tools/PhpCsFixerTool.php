<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

class PhpCsFixerTool extends AbstractTool
{
    public function __construct()
    {
        parent::__construct('php-cs-fixer', 'friendsofphp/php-cs-fixer', 'PHP CS Fixer (code style fixes)');
    }
}
