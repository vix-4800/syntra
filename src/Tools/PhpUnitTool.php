<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

class PhpUnitTool extends AbstractTool
{
    public function __construct()
    {
        parent::__construct('phpunit', 'phpunit/phpunit', 'PHPUnit (running tests)');
    }
}
