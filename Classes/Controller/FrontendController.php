<?php

namespace T3\Vici\Controller;

use Psr\Http\Message\ResponseInterface;
use T3\Vici\Generator\StaticValues;
use T3\Vici\Model\GenericViciModel;
use T3\Vici\Repository\ViciFrontendRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

class FrontendController extends ActionController
{
    public function __construct(
        private readonly ViciFrontendRepository $viciFrontendRepository,
        private readonly ViciRepository $viciRepository,
        private readonly StaticValues $staticValues,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        if ($contentObject instanceof ContentObjectRenderer) {
            $contentObject = $contentObject->data;
            $this->view->assign('contentObject', $contentObject);
        }

        $pageInformation = $this->request->getAttribute('frontend.page.information');
        if ($pageInformation instanceof PageInformation) {
            $this->view->assign('pageInformation', $pageInformation);
        }

        if (!array_key_exists('tx_vici_table', $contentObject ?? [])) {
            throw new \InvalidArgumentException('No vici table defined in content element with uid ' . ($contentObject['uid'] ?? 0));
        }

        $tableRow = $this->viciRepository->findTableByUid($contentObject['tx_vici_table']);
        if (!$tableRow || $tableRow['hidden']) {
            return $this->getErrorHtmlResponse('Vici table with uid ' . $contentObject['tx_vici_table'] . ' not found or hidden!');
        }
        $tableColumns = $this->viciRepository->findTableColumnsByTableUid($tableRow['uid']);
        if (empty($tableColumns)) {
            return $this->getErrorHtmlResponse('Vici table with uid ' . $contentObject['tx_vici_table'] . ' has no columns configured!');
        }

        /** @var class-string<GenericViciModel> $className */
        $className = $this->staticValues->getProxyClassNamespace(GeneralUtility::underscoredToUpperCamelCase($tableRow['name']));
        $this->viciFrontendRepository->setObjectType($className);
        if (!empty($tableRow['enable_column_sorting'])) {
            $this->viciFrontendRepository->setDefaultOrderings(['sorting' => 'ASC']);
        }

        $records = $this->viciFrontendRepository->findAll();

        /** @var FluidViewAdapter $fluidViewAdapter */
        $fluidViewAdapter = $this->view;
        $fluidViewAdapter->getRenderingContext()->getTemplatePaths()->setTemplateSource($contentObject['tx_vici_template'] ?? '');

        $this->view->assign('records', $records);

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
