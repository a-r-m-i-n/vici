<?php

namespace T3\Vici\Controller;

use Psr\Http\Message\ResponseInterface;
use T3\Vici\FrontendPlugin\FrontendPlugin;
use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use T3\Vici\FrontendPlugin\PageTitleProvider;
use T3\Vici\Generator\StaticValues;
use T3\Vici\Model\GenericViciModel;
use T3\Vici\Repository\ViciFrontendRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

class FrontendController extends ActionController
{
    private FrontendPlugin $frontendPlugin;

    /** @var array<string, mixed> */
    private array $tableRow;

    public function __construct(
        private readonly FrontendPluginRepository $frontendPluginRepository,
        private readonly ViciFrontendRepository $viciFrontendRepository,
        private readonly ViciRepository $viciRepository,
        private readonly StaticValues $staticValues,
    ) {
    }

    protected function initializeAction(): void
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        if ($contentObject instanceof ContentObjectRenderer) {
            $this->frontendPlugin = $this->frontendPluginRepository->createFrontendPluginInstance($contentObject->data);
        } else {
            throw new \InvalidArgumentException('VICI frontend plugin not found!');
        }

        if (!$this->frontendPlugin->getViciTableUid()) {
            throw new \InvalidArgumentException('No VICI table defined in content element with uid ' . $this->frontendPlugin->getUid());
        }

        // Check VICI configuration and configure viciFrontendRepository
        $this->tableRow = $this->viciRepository->findTableByUid($this->frontendPlugin->getViciTableUid()) ?? [];
        if (empty($this->tableRow) || $this->tableRow['hidden']) {
            throw new \InvalidArgumentException('VICI table with uid ' . $this->frontendPlugin->getViciTableUid() . ' not found or hidden!');
        }
        $tableColumns = $this->viciRepository->findTableColumnsByTableUid($this->tableRow['uid']);
        if (empty($tableColumns)) {
            throw new \InvalidArgumentException('VICI table with uid ' . $this->frontendPlugin->getViciTableUid() . ' has no columns configured!');
        }

        /** @var class-string<GenericViciModel> $className */
        $className = $this->staticValues->getProxyClassNamespace(GeneralUtility::underscoredToUpperCamelCase($this->tableRow['name']));
        $this->viciFrontendRepository->setObjectType($className);
    }

    protected function resolveView(): FluidStandaloneViewInterface|ViewInterface
    {
        $view = parent::resolveView();

        $view->assign('frontendPlugin', $this->frontendPlugin);

        $pageInformation = $this->request->getAttribute('frontend.page.information');
        if ($pageInformation instanceof PageInformation) {
            $view->assign('pageInformation', $pageInformation);
        }

        return $view;
    }

    public function indexAction(int $currentPageNumber = 1): ResponseInterface
    {
        if (!empty($this->tableRow['enable_column_sorting'])) {
            $this->viciFrontendRepository->setDefaultOrderings(['sorting' => 'ASC']);
        }

        $records = $this->viciFrontendRepository->findAll();

        if ($this->frontendPlugin->isPaginationEnabled()) {
            /** @var QueryResultPaginator $paginator */
            $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $records, $currentPageNumber, $this->frontendPlugin->getPaginationItemsPerPage());
            $pagination = GeneralUtility::makeInstance($this->frontendPlugin->getPaginationType(), $paginator, $this->frontendPlugin->getPaginationMaxLinks());
            $this->view->assign('currentPageNumber', $currentPageNumber);
            $this->view->assign('pagination', $pagination);
            $this->view->assign('records', $paginator->getPaginatedItems());
        } else {
            $this->view->assign('records', $records);
        }

        /** @var FluidViewAdapter $fluidViewAdapter */
        $fluidViewAdapter = $this->view;
        $fluidViewAdapter->getRenderingContext()->getTemplatePaths()->setTemplateSource($this->frontendPlugin->getViciTemplate());

        return $this->htmlResponse();
    }

    public function showAction(int $uid): ResponseInterface
    {
        if (!$this->frontendPlugin->isDetailpageEnabled()) {
            return $this->getErrorHtmlResponse('Page not found.');
        }

        /** @var GenericViciModel|null $record */
        $record = $this->viciFrontendRepository->findByUid($uid);
        $this->view->assign('record', $record);

        if ($record && $this->frontendPlugin->getPageTitleMode() && $this->frontendPlugin->getPageTitleColumnUid()) {
            /** @var PageTitleProvider $pageTitleProvider */
            $pageTitleProvider = GeneralUtility::makeInstance(PageTitleProvider::class);
            $pageTitleProvider->generate($record, $this->frontendPlugin);
        }

        /** @var FluidViewAdapter $fluidViewAdapter */
        $fluidViewAdapter = $this->view;
        $fluidViewAdapter->getRenderingContext()->getTemplatePaths()->setTemplateSource($this->frontendPlugin->getViciDetailpageTemplate());

        return $this->htmlResponse();
    }

    private function getErrorHtmlResponse(string $message): ResponseInterface
    {
        $html = <<<HTML
                <p style="border: 1px solid red; background: #fdd; padding: 1rem;"><strong>VICI Error:</strong> $message</p>
            HTML;

        return $this->htmlResponse($html)->withStatus(404);
    }
}
