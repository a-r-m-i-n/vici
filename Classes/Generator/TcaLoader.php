<?php

namespace T3\Vici\Generator;

use Symfony\Component\Finder\Finder;

readonly class TcaLoader
{
    public function __construct(private StaticValues $staticValues)
    {
    }

    /**
     * @return array<string, mixed> Key is the table name, value the generated TCA
     */
    public function __invoke(): array
    {
        $path = $this->staticValues->getCachePathForTca();
        if (!file_exists($path)) {
            return [];
        }

        $finder = new Finder();
        $files = $finder->files()
            ->in($path)
            ->name($this->staticValues->getTableNamePrefix() . '*.php')
            ->depth(0)
        ;

        $loadedTca = [];
        foreach ($files as $file) {
            $loadedTca[$file->getFilenameWithoutExtension()] = require $file->getRealPath();
        }

        return $loadedTca;
    }
}
