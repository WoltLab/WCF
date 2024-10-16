<?php

namespace wcf\page;

use wcf\system\request\LinkHandler;
use wcf\system\view\grid\AbstractGridView;
use wcf\system\WCF;

abstract class AbstractGridViewPage extends AbstractPage
{
    protected AbstractGridView $gridView;
    protected int $pageNo = 1;
    protected string $sortField = '';
    protected string $sortOrder = '';
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
        ]);
    }

    protected function initGridView(): void
    {
        $this->gridView = $this->createGridViewController();

        if ($this->sortField) {
            $this->gridView->setSortField($this->sortField);
        }
        if ($this->sortOrder) {
            $this->gridView->setSortOrder($this->sortOrder);
        }
        if ($this->filters !== []) {
            $this->gridView->setActiveFilters($this->filters);
        }
        $this->gridView->setPageNo($this->pageNo);
        $this->gridView->setBaseUrl(LinkHandler::getInstance()->getControllerLink(static::class));
    }

    protected abstract function createGridViewController(): AbstractGridView;
}
