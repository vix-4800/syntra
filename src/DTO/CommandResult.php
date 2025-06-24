<?php

declare(strict_types=1);

namespace Vix\Syntra\DTO;

use ArrayAccess;
use LogicException;
use Vix\Syntra\Enums\CommandStatus;

class CommandResult implements ArrayAccess
{
    public function __construct(
        public readonly CommandStatus $status,
        public readonly array $messages,
    ) {
        //
    }

    public static function ok(array $messages = []): self
    {
        return new self(CommandStatus::OK, $messages);
    }

    public static function warning(array $messages): self
    {
        return new self(CommandStatus::WARNING, $messages);
    }

    public static function error(array $messages): self
    {
        return new self(CommandStatus::WARNING, $messages);
    }

    public function offsetExists($offset): bool
    {
        return in_array($offset, ['status', 'messages']);
    }

    public function offsetGet($offset): mixed
    {
        return match ($offset) {
            'status' => $this->status,
            'messages' => $this->messages,
            default => null,
        };
    }

    public function offsetSet($offset, $value): void
    {
        throw new LogicException('CommandResult is immutable');
    }

    public function offsetUnset($offset): void
    {
        throw new LogicException('CommandResult is immutable');
    }

    public function isOk(): bool
    {
        return $this->status === CommandStatus::OK;
    }

    public function hasWarnings(): bool
    {
        return $this->status === CommandStatus::WARNING;
    }
}
