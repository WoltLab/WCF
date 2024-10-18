<?php

namespace wcf\acp\page;

use wcf\data\application\Application;
use wcf\data\application\ApplicationList;
use wcf\data\page\PageList;
use wcf\page\SortablePage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of pages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property    PageList $objectList
 */
class PageListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';

    /**
     * @inheritDoc
     */
    public $objectListClassName = PageList::class;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManagePage'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'name';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['pageID', 'name', 'lastUpdateTime', 'pageType'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * name
     * @var string
     */
    public $name = '';

    /**
     * title
     * @var string
     */
    public $title = '';

    /**
     * content
     * @var string
     */
    public $content = '';

    /**
     * application id of the page
     * @var int
     */
    public $applicationPackageID = 0;

    /**
     * page type
     * @var string
     */
    public $pageType = '';

    /**
     * list of available applications
     * @var Application[]
     */
    public $availableApplications = [];

    /**
     * display 'Add Page' dialog on load
     * @var bool
     */
    public $showPageAddDialog = 0;

    /**
     * filters the list of pages showing only custom pages
     * @var bool
     */
    public $originIsNotSystem = 0;

    /**
     * filters the list of pages showing only pages with custom urls
     * @var bool
     */
    public $controllerCustomURL = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['name'])) {
            $this->name = StringUtil::trim($_REQUEST['name']);
        }
        if (!empty($_REQUEST['title'])) {
            $this->title = StringUtil::trim($_REQUEST['title']);
        }
        if (!empty($_REQUEST['content'])) {
            $this->content = StringUtil::trim($_REQUEST['content']);
        }
        if (isset($_REQUEST['applicationPackageID'])) {
            $this->applicationPackageID = \intval($_REQUEST['applicationPackageID']);
        }
        if (!empty($_REQUEST['pageType'])) {
            $this->pageType = $_REQUEST['pageType'];
        }
        if (!empty($_REQUEST['showPageAddDialog'])) {
            $this->showPageAddDialog = 1;
        }
        if (!empty($_REQUEST['originIsNotSystem'])) {
            $this->originIsNotSystem = 1;
        }
        if (!empty($_REQUEST['controllerCustomURL'])) {
            $this->controllerCustomURL = 1;
        }

        // get available applications
        $applicationList = new ApplicationList();
        $applicationList->readObjects();
        $this->availableApplications = $applicationList->getObjects();
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if (!empty($this->name)) {
            $this->objectList->getConditionBuilder()->add('page.name LIKE ?', ['%' . $this->name . '%']);
        }
        if (!empty($this->title)) {
            $this->objectList->getConditionBuilder()->add(
                'page.pageID IN (
                    SELECT  pageID
                    FROM    wcf1_page_content
                    WHERE   title LIKE ?
                )',
                ['%' . $this->title . '%']
            );
        }
        if (!empty($this->content)) {
            $this->objectList->getConditionBuilder()->add(
                'page.pageID IN (
                    SELECT  pageID
                    FROM    wcf1_page_content
                    WHERE   content LIKE ?
                )',
                ['%' . $this->content . '%']
            );
        }
        if (!empty($this->applicationPackageID)) {
            $this->objectList->getConditionBuilder()->add(
                '((page.applicationPackageID = ? AND page.overrideApplicationPackageID IS NULL) OR page.overrideApplicationPackageID = ?)',
                [$this->applicationPackageID, $this->applicationPackageID]
            );
        }
        if (!empty($this->pageType)) {
            $this->objectList->getConditionBuilder()->add('page.pageType = (?)', [$this->pageType]);
        }
        if ($this->originIsNotSystem) {
            $this->objectList->getConditionBuilder()->add('page.originIsSystem = ?', [0]);
        }
        if ($this->controllerCustomURL) {
            $this->objectList->getConditionBuilder()->add(
                "(page.controllerCustomURL <> ? OR page.pageType <> ?)",
                ['', 'system']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'name' => $this->name,
            'title' => $this->title,
            'content' => $this->content,
            'applicationPackageID' => $this->applicationPackageID,
            'pageType' => $this->pageType,
            'availableApplications' => $this->availableApplications,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'showPageAddDialog' => $this->showPageAddDialog,
            'originIsNotSystem' => $this->originIsNotSystem,
            'controllerCustomURL' => $this->controllerCustomURL,
        ]);
    }
}
