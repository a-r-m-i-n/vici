<?php

namespace T3\Vici\Generator;

use TYPO3\CMS\Core\Core\Environment;

class StaticValues
{
    private const TABLE_PREFIX = 'tx_vici_custom_';

    private const CACHE_PATH_TCA = '/cache/code/vici/';
    private const CACHE_PATH_PROXY_CLASSES = '/cache/code/vici/extbase/';

    private const PROXY_CLASS_NAMESPACE = 'T3\\Vici\\Custom\\Domain\\Model';

    // Table name related

    public function getTableNamePrefix(): string
    {
        return self::TABLE_PREFIX;
    }

    /**
     * @return string e.g. 'tx_vici_custom_example' when given $name is 'example'
     */
    public function getFullTableName(string $name): string
    {
        return $this->getTableNamePrefix() . $name;
    }

    // Cache path related

    public function getCachePathForTca(?string $fileName = null): string
    {
        $path = Environment::getVarPath() . self::CACHE_PATH_TCA;
        if ($fileName) {
            $path .= $fileName;
        }

        return $path;
    }

    public function getCachePathForProxyClasses(?string $fileName = null): string
    {
        $path = Environment::getVarPath() . self::CACHE_PATH_PROXY_CLASSES;
        if ($fileName) {
            $path .= $fileName;
        }

        return $path;
    }

    // Proxy class related

    public function getProxyClassNamespace(?string $className = null): string
    {
        $fqcn = self::PROXY_CLASS_NAMESPACE;
        if ($className) {
            $fqcn .= '\\' . $className;
        }

        return $fqcn;
    }
}
