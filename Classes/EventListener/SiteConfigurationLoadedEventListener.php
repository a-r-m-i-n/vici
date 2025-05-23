<?php

namespace T3\Vici\EventListener;

use T3\Vici\FrontendPlugin\FrontendPlugin;
use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationLoadedEvent;

#[AsEventListener(
    identifier: 'vici/site-configuration-loaded',
)]
readonly class SiteConfigurationLoadedEventListener
{
    public function __construct(private FrontendPluginRepository $frontendPluginRepository)
    {
    }

    public function __invoke(SiteConfigurationLoadedEvent $event): void
    {
        $configuration = $event->getConfiguration();

        $rootPageId = $configuration['rootPageId'] ?? null;
        if (!$rootPageId) {
            return;
        }

        foreach ($this->frontendPluginRepository->findAll() as $frontendPlugin) {
            if ($frontendPlugin->isInRootline($rootPageId)) {
                // Check settings of frontendPlugin and register route Enhancers, if necessary
                $configuration = $this->registerRouteEnhancer($configuration, $frontendPlugin);
            }
        }

        $event->setConfiguration($configuration);
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<string, mixed>
     */
    private function registerRouteEnhancer(array $configuration, FrontendPlugin $frontendPlugin): array
    {
        if (!array_key_exists('routeEnhancers', $configuration)) {
            $configuration['routeEnhancers'] = [];
        }

        if ($frontendPlugin->isPaginationEnabled()) {
            $configuration['routeEnhancers']['viciPagination'] = [
                'type' => 'Extbase',
                'extension' => 'Vici',
                'plugin' => 'Frontend',
                'routes' => [
                    [
                        'routePath' => '/',
                        '_controller' => 'Frontend::index',
                    ],
                    [
                        'routePath' => '/{localized_page}-{page}',
                        '_controller' => 'Frontend::index',
                        '_arguments' => ['page' => 'currentPageNumber'],
                    ],
                ],
                'defaultController' => 'Frontend::index',
                'defaults' => ['page' => '0'],
                'aspects' => [
                    'localized_page' => [
                        'type' => 'LocaleModifier',
                        'default' => 'page',
                        'localeMap' => [
                            ['locale' => 'de_*', 'value' => 'seite'],
                            ['locale' => 'es_*|pt_*|it_*|nl_*', 'value' => 'pagina'],
                            ['locale' => 'tr_*', 'value' => 'sayfa'],
                            ['locale' => 'zh_*', 'value' => 'ye'],
                            ['locale' => 'th_*', 'value' => 'nai'],
                            ['locale' => 'ar_*', 'value' => 'safha'],
                            ['locale' => 'ja_*', 'value' => 'peji'],
                            ['locale' => 'pl_*', 'value' => 'strona'],
                        ],
                    ],
                    'page' => [
                        'type' => 'StaticRangeMapper',
                        'start' => '1',
                        'end' => '1000',
                    ],
                ],
            ];
        }

        return $configuration;
    }
}
