<?php

namespace wcf\page;

use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\gridView\AbstractGridView;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a page that is rendering a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TGridView of AbstractGridView
 */
abstract class AbstractGridViewPage extends AbstractPage
{
    /**
     * @var TGridView
     */
    protected AbstractGridView $gridView;
    protected int $pageNo = 1;
    protected string $sortField = '';
    protected string $sortOrder = '';

    /**
     * @var mixed[]
     */
    protected array $filters = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['pageNo'])) {
            $this->pageNo = \intval($_REQUEST['pageNo']);
        }
        if (isset($_REQUEST['sortField'])) {
            $this->sortField = $_REQUEST['sortField'];
        }
        if (isset($_REQUEST['sortOrder']) && ($_REQUEST['sortOrder'] === 'ASC' || $_REQUEST['sortOrder'] === 'DESC')) {
            $this->sortOrder = $_REQUEST['sortOrder'];
        }
        if (isset($_REQUEST['filters']) && \is_array($_REQUEST['filters'])) {
            $this->filters = $_REQUEST['filters'];
        }

        $this->canonicalURL = $this->getCanonicalUrl();
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->initGridView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'gridView' => $this->gridView,
            'headContent' => $this->getHeadContent(),
        ]);
    }

    protected function initGridView(): void
    {
        $this->gridView = $this->createGridView();
        if (!$this->gridView->isAccessible()) {
            throw new PermissionDeniedException();
        }

        if ($this->sortField) {
            $this->gridView->setSortField($this->sortField);
        }
        if ($this->sortOrder) {
            $this->gridView->setSortOrder($this->sortOrder);
        }
        if ($this->filters !== []) {
            $this->gridView->setActiveFilters($this->filters);
        }
        if ($this->pageNo != 1) {
            $this->gridView->setPageNo($this->pageNo);
        }
        $this->gridView->setBaseUrl(
            LinkHandler::getInstance()->getControllerLink(static::class, $this->getBaseUrlParameters())
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBaseUrlParameters(): array
    {
        return [];
    }

    protected function getHeadContent(): string
    {
        $linkTags = [];
        if ($this->gridView->getPageNo() < $this->gridView->countPages()) {
            $linkTags[] = \sprintf(
                '<link rel="next" href="%s">',
                StringUtil::encodeHTML(
                    LinkHandler::getInstance()->getControllerLink(static::class, \array_merge(
                        $this->getBaseUrlParameters(),
                        [
                            'pageNo' => $this->gridView->getPageNo() + 1,
                            'sortField' => $this->sortField ?: null,
                            'sortOrder' => $this->sortOrder ?: null,
                        ]
                    ))
                )
            );
        }

        if ($this->gridView->getPageNo() > 1) {
            $linkTags[] = \sprintf(
                '<link rel="prev" href="%s">',
                StringUtil::encodeHTML(
                    LinkHandler::getInstance()->getControllerLink(static::class, \array_merge(
                        $this->getBaseUrlParameters(),
                        [
                            'pageNo' => $this->gridView->getPageNo() !== 2 ? $this->gridView->getPageNo() - 1 : null,
                            'sortField' => $this->sortField ?: null,
                            'sortOrder' => $this->sortOrder ?: null,
                        ]
                    ))
                )
            );
        }

        return \implode("\n", $linkTags);
    }

    protected function getCanonicalUrl(): string
    {
        return LinkHandler::getInstance()->getControllerLink(static::class, \array_merge(
            $this->getBaseUrlParameters(),
            [
                'pageNo' => $this->pageNo !== 1 ? $this->pageNo : null,
            ]
        ));
    }

    /**
     * Returns the grid view instance for the rendering of this page.
     *
     * @return TGridView
     */
    protected abstract function createGridView(): AbstractGridView;
}
