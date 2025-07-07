<?php

declare(strict_types=1);

namespace Vix\Syntra\DTO;

use ArrayAccess;
use LogicException;
use Vix\Syntra\Enums\CommandStatus;

class CommandResult implements ArrayAccess
{
/**
 * Class constructor.
 */
    public function __construct(
        public readonly CommandStatus $status,
        public readonly array $messages,
    ) {
        //
    }

/**
 * Return a successful result.
 */
    public static function ok(array $messages = []): self
    {
        return new self(CommandStatus::OK, $messages);
    }

/**
 * Return a warning result.
 */
    public static function warning(array $messages): self
    {
        return new self(CommandStatus::WARNING, $messages);
    }

/**
 * Return an error result.
 */
    public static function error(array $messages): self
    {
        return new self(CommandStatus::ERROR, $messages);
    }

/**
 * Check whether the given offset exists.
 */
    public function offsetExists($offset): bool
    {
        return in_array($offset, ['status', 'messages']);
    }

/**
 * Get the value for a specific offset.
 */
    public function offsetGet($offset): mixed
    {
        return match ($offset) {
            'status' => $this->status,
            'messages' => $this->messages,
            default => null,
        };
    }

/**
 * Disallow setting values via ArrayAccess.
 */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('CommandResult is immutable');
    }

/**
 * Disallow unsetting values via ArrayAccess.
 */
    public function offsetUnset($offset): void
    {
        throw new LogicException('CommandResult is immutable');
    }

/**
 * Determine whether the command succeeded.
 */
    public function isOk(): bool
    {
        return $this->status === CommandStatus::OK;
    }

/**
 * Determine whether there were any warnings.
 */
    public function hasWarnings(): bool
    {
        return $this->status === CommandStatus::WARNING;
    }
}
