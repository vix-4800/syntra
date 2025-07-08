<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

class PhpStanTool extends AbstractTool
{
    public function __construct()
    {
        parent::__construct('phpstan', 'phpstan/phpstan', 'PHPStan (static analysis)');
    }
}
