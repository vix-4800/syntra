<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Vix\Syntra\Facades\File;
use Vix\Syntra\Traits\AnalyzesFilesTrait;

/**
 * Provides a reusable file scanning helper for analyze commands.
 */
trait PatternFinderTrait
{
    use AnalyzesFilesTrait;

    /**
     * Iterate over project files and invoke a callback for each regex match.
     *
     * @param array<string,string>                                   $patterns label => regex
     * @param callable(string,int,string,string,array):void $onMatch
     *        Receives relative file path, line number, label, line text and preg matches.
     */
    protected function scanFilesForPatterns(
        array $patterns,
        callable $onMatch,
        ?string $skipFileContains = null
    ): void {
        $this->analyzeFiles(function (string $filePath) use ($patterns, $onMatch, $skipFileContains): void {
            if ($skipFileContains !== null && str_contains((string) $filePath, $skipFileContains)) {
                return;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                return;
            }

            $relativePath = File::makeRelative($filePath, $this->path);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                foreach ($patterns as $label => $pattern) {
                    if (preg_match($pattern, $line, $m)) {
                        $onMatch($relativePath, $lineNumber + 1, $label, $line, $m);
                        break;
                    }
                }
            }
        });
    }
}
