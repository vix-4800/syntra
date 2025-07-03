<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

class StubHelper
{
    private string $stubPath;

    public function __construct(string $stubName)
    {
        if (!str_ends_with($stubName, ".stub")) {
            $stubName .= ".stub";
        }

        $this->stubPath = PACKAGE_ROOT . "/stubs/{$stubName}";
    }

    public function render(array $replacements): string
    {
        $content = file_get_contents($this->stubPath);

        foreach ($replacements as $key => $value) {
            if (!$value) {
                continue;
            }

            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        return $content;
    }
}
