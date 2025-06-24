<?php

declare(strict_types=1);

namespace Vix\Syntra\Enums;

enum CommandStatus: string
{
    case OK = 'ok';
    case WARNING = 'warning';
    case ERROR = 'error';
}
