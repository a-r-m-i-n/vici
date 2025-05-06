<?php

namespace T3\Vici\UserFunction\ItemsProcFunc;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class Icons
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function getAvailableIcons(array &$parameters): void
    {
        // TYPO3 Core Icons
        $parameters['items'][] = ['Core icons', '--div--'];

        $absoluteIconDeclarationPath = GeneralUtility::getFileAbsFileName('EXT:core/Resources/Public/Icons/T3Icons/icons.json');
        $json = json_decode(file_get_contents($absoluteIconDeclarationPath) ?: '', true, 512, JSON_THROW_ON_ERROR);
        foreach ($json['icons'] ?? [] as $declaration) {
            $parameters['items'][] = [$declaration['identifier'], $declaration['identifier'], $declaration['identifier']];
        }

        // TYPO3 Extension Icons
        $parameters['items'][] = ['Extension icons', '--div--'];

        /** @var array<string, mixed[]> $extensionIcons */
        $extensionIcons = $this->container->get('icons');
        foreach ($extensionIcons as $identifier => $config) {
            $parameters['items'][] = [$identifier, $identifier, $identifier];
        }
    }
}
