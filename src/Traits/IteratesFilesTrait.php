<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

trait IteratesFilesTrait
{
    /**
     * Iterate over the given items with progress indicators.
     *
     * @template T
     * @param array<T>         $items
     * @param callable(T):void $callback
     */
    protected function iterateFiles(array $items, callable $callback): void
    {
        $this->setProgressMax(count($items));
        $this->startProgress();

        foreach ($items as $item) {
            $callback($item);
            $this->advanceProgress();
        }

        $this->finishProgress();
    }
}
