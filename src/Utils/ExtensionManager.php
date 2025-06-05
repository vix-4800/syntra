<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

class ExtensionManager
{
    /** @var object[] */
    private array $extensions = [];

    public function __construct(
        private ConfigLoader $configLoader
    ) {
        $this->configLoader = $configLoader;
    }

    /**
     * @return object[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
