<?php

namespace T3\Vici\Controller;

use Psr\Http\Message\ResponseInterface;
use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use T3\Vici\Generator\StaticValues;
use T3\Vici\Model\GenericViciModel;
use T3\Vici\Repository\ViciFrontendRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

class FrontendController extends ActionController
{
    public function __construct(
        private readonly FrontendPluginRepository $frontendPluginRepository,
        private readonly ViciFrontendRepository $viciFrontendRepository,
        private readonly ViciRepository $viciRepository,
        private readonly StaticValues $staticValues,
    ) {
    }

    public function indexAction(int $currentPageNumber = 1): ResponseInterface
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        if ($contentObject instanceof ContentObjectRenderer) {
            $frontendPlugin = $this->frontendPluginRepository->createFrontendPluginInstance($contentObject->data);
            $this->view->assign('frontendPlugin', $frontendPlugin);
            unset($contentObject);
        } else {
            throw new \InvalidArgumentException('VICI frontend plugin not found!');
        }

        $pageInformation = $this->request->getAttribute('frontend.page.information');
        if ($pageInformation instanceof PageInformation) {
            $this->view->assign('pageInformation', $pageInformation);
        }

        if (!$frontendPlugin->getViciTableUid()) {
            throw new \InvalidArgumentException('No vici table defined in content element with uid ' . $frontendPlugin->getUid());
        }

        $tableRow = $this->viciRepository->findTableByUid($frontendPlugin->getViciTableUid());
        if (!$tableRow || $tableRow['hidden']) {
            return $this->getErrorHtmlResponse('Vici table with uid ' . $frontendPlugin->getViciTableUid() . ' not found or hidden!');
        }
        $tableColumns = $this->viciRepository->findTableColumnsByTableUid($tableRow['uid']);
        if (empty($tableColumns)) {
            return $this->getErrorHtmlResponse('Vici table with uid ' . $frontendPlugin->getViciTableUid() . ' has no columns configured!');
        }

        /** @var class-string<GenericViciModel> $className */
        $className = $this->staticValues->getProxyClassNamespace(GeneralUtility::underscoredToUpperCamelCase($tableRow['name']));
        $this->viciFrontendRepository->setObjectType($className);
        if (!empty($tableRow['enable_column_sorting'])) {
            $this->viciFrontendRepository->setDefaultOrderings(['sorting' => 'ASC']);
        }

        $records = $this->viciFrontendRepository->findAll();

        if ($frontendPlugin->isPaginationEnabled()) {
            /** @var QueryResultPaginator $paginator */
            $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $records, $currentPageNumber, $frontendPlugin->getPaginationItemsPerPage());
            $pagination = GeneralUtility::makeInstance($frontendPlugin->getPaginationType(), $paginator, $frontendPlugin->getPaginationMaxLinks());
            $this->view->assign('currentPageNumber', $currentPageNumber);
            $this->view->assign('pagination', $pagination);
            $this->view->assign('records', $paginator->getPaginatedItems());
        } else {
            $this->view->assign('records', $records);
        }

        /** @var FluidViewAdapter $fluidViewAdapter */
        $fluidViewAdapter = $this->view;
        $fluidViewAdapter->getRenderingContext()->getTemplatePaths()->setTemplateSource($frontendPlugin->getViciTemplate());

        return $this->htmlResponse();
    }

    private function getErrorHtmlResponse(string $message): ResponseInterface
    {
        $html = <<<HTML
                <p style="border: 1px solid red; background: #fdd; padding: 1rem;"><strong>Vici Error:</strong> $message</p>
            HTML;

        return $this->htmlResponse($html)->withStatus(404);
    }
}
