<?php

namespace T3\Vici\Generator;

use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class ProxyClassLoader
{
    public function __construct(private StaticValues $staticValues)
    {
    }

    public function registerAutoloader(): void
    {
        spl_autoload_register([GeneralUtility::makeInstance(self::class), 'loadClass'], true, true);
    }

    private function loadClass(string $className): bool
    {
        if (str_starts_with($className, $this->staticValues->getProxyClassNamespace())) {
            $classNameParts = GeneralUtility::trimExplode('\\', $className, true);
            $modelName = array_pop($classNameParts);
            $destinationPath = $this->staticValues->getCachePathForProxyClasses($modelName . '.php');

            if (file_exists($destinationPath)) {
                require_once $destinationPath;
            }
        }

        return true;
    }
}
