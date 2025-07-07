<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Output that writes messages to two underlying outputs simultaneously.
 */
class TeeOutput extends Output
{
    public function __construct(private readonly OutputInterface $first, private readonly OutputInterface $second)
    {
        parent::__construct($first->getVerbosity(), $first->isDecorated(), $first->getFormatter());
    }

    protected function doWrite(string $message, bool $newline): void
    {
        $this->first->write($message, $newline);
        $this->second->write($message, $newline);
    }

    public function setDecorated(bool $decorated): void
    {
        parent::setDecorated($decorated);
        $this->first->setDecorated($decorated);
        $this->second->setDecorated($decorated);
    }

    public function setVerbosity(int $level): void
    {
        parent::setVerbosity($level);
        $this->first->setVerbosity($level);
        $this->second->setVerbosity($level);
    }
}
