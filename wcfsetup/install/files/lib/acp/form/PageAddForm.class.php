<?php

namespace wcf\acp\form;

use wcf\data\application\Application;
use wcf\data\application\ApplicationList;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\language\Language;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemAction;
use wcf\data\menu\item\MenuItemEditor;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\menu\MenuCache;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageEditor;
use wcf\data\page\PageNodeTree;
use wcf\data\smiley\SmileyCache;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the page add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class PageAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.page.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManagePage'];

    /**
     * true if created page is multi-lingual
     * @var bool
     */
    public $isMultilingual = 0;

    /**
     * page type
     * @var string
     */
    public $pageType = '';

    /**
     * parent page id
     * @var int
     */
    public $parentPageID = 0;

    /**
     * page name
     * @var string
     */
    public $name = '';

    /**
     * true if page is disabled
     * @var bool
     */
    public $isDisabled = 0;

    /**
     * application id of the page
     * @var int
     */
    public $applicationPackageID = 1;

    /**
     * list of available applications
     * @var Application[]
     */
    public $availableApplications = [];

    /**
     * list of available boxes
     * @var Box[]
     */
    public $availableBoxes = [];

    /**
     * list of available languages
     * @var Language[]
     */
    public $availableLanguages = [];

    /**
     * page custom URL
     * @var string[]
     */
    public $customURL = [];

    /**
     * page titles
     * @var string[]
     */
    public $title = [];

    /**
     * page contents
     * @var string[]
     */
    public $content = [];

    /**
     * page meta descriptions
     * @var string[]
     */
    public $metaDescription = [];

    /**
     * list of box ids
     * @var int[]
     */
    public $boxIDs = [];

    /**
     * acl values
     * @var array
     */
    public $aclValues = [];

    /**
     * @var HtmlInputProcessor[]
     */
    public $htmlInputProcessors = [];

    /**
     * css class name of created page
     * @var string
     */
    public $cssClassName = '';

    /**
     * true if the page is available during offline mode
     * @var bool
     */
    public $availableDuringOfflineMode = 0;

    /**
     * true if the page is accessible for search spiders
     * @var bool
     */
    public $allowSpidersToIndex = 1;

    /**
     * true if page should be added to the main menu
     * @var bool
     */
    public $addPageToMainMenu = 0;

    /**
     * parent menu item id
     * @var int
     */
    public $parentMenuItemID;

    /**
     * menu item node tree
     * @var MenuItemNodeTree
     */
    public $menuItems;

    /**
     * @var bool
     * @since   5.2
     */
    public $enableShareButtons = 0;

    /**
     * @var int
     * @since   5.2
     */
    public $presetPageID = 0;

    /**
     * @var Page
     * @since   5.2
     */
    public $presetPage;

    /**
     * @var bool
     * @since   5.4
     */
    public $invertPermissions = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get available applications
        $applicationList = new ApplicationList();
        $applicationList->readObjects();
        $this->availableApplications = $applicationList->getObjects();

        // get available languages
        $this->availableLanguages = LanguageFactory::getInstance()->getLanguages();

        // get available boxes
        $boxList = new BoxList();
        $boxList->sqlOrderBy = 'box.name';
        $boxList->readObjects();
        $this->availableBoxes = $boxList->getObjects();

        if (isset($_GET['presetPageID'])) {
            $this->presetPageID = \intval($_GET['presetPageID']);
        }
        if ($this->presetPageID) {
            $this->presetPage = new Page($this->presetPageID);
            if (!$this->presetPage->pageID || $this->presetPage->pageType === 'system') {
                throw new IllegalLinkException();
            }
        }

        $this->readPageType();
    }

    /**
     * Reads basic page parameters controlling type and i18n.
     *
     * @throws  IllegalLinkException
     */
    protected function readPageType()
    {
        if ($this->presetPage) {
            $this->isMultilingual = $this->presetPage->isMultilingual;
            $this->pageType = $this->presetPage->pageType;

            return;
        }

        if (!empty($_REQUEST['isMultilingual'])) {
            $this->isMultilingual = 1;
        }
        if (!empty($_REQUEST['pageType'])) {
            $this->pageType = $_REQUEST['pageType'];
        }

        // work-around to force adding pages via dialog overlay
        if (empty($_POST) && $this->pageType == '') {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('PageList', ['showPageAddDialog' => 1]));

            exit;
        }

        try {
            $this->validatePageType();
        } catch (UserInputException $e) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->allowSpidersToIndex = 0;
        if (isset($_POST['parentPageID'])) {
            $this->parentPageID = \intval($_POST['parentPageID']);
        }
        if (isset($_POST['name'])) {
            $this->name = StringUtil::trim($_POST['name']);
        }
        if (isset($_POST['cssClassName'])) {
            $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
        }
        if (isset($_POST['isDisabled'])) {
            $this->isDisabled = 1;
        }
        if (isset($_POST['availableDuringOfflineMode'])) {
            $this->availableDuringOfflineMode = 1;
        }
        if (isset($_POST['allowSpidersToIndex'])) {
            $this->allowSpidersToIndex = 1;
        }
        if (isset($_POST['addPageToMainMenu'])) {
            $this->addPageToMainMenu = 1;
        }
        if (isset($_POST['applicationPackageID'])) {
            $this->applicationPackageID = \intval($_POST['applicationPackageID']);
        }
        if (!empty($_POST['parentMenuItemID'])) {
            $this->parentMenuItemID = \intval($_POST['parentMenuItemID']);
        }
        if (isset($_POST['enableShareButtons'])) {
            $this->enableShareButtons = 1;
        }

        if (isset($_POST['customURL']) && \is_array($_POST['customURL'])) {
            $this->customURL = \array_map($this->normalizeCustomUrl(...), ArrayUtil::trim($_POST['customURL']));
        }
        if (isset($_POST['title']) && \is_array($_POST['title'])) {
            $this->title = ArrayUtil::trim($_POST['title']);
        }
        if (isset($_POST['content']) && \is_array($_POST['content'])) {
            $this->content = ArrayUtil::trim($_POST['content']);
        }
        if (isset($_POST['metaDescription']) && \is_array($_POST['metaDescription'])) {
            $this->metaDescription = ArrayUtil::trim($_POST['metaDescription']);
        }
        if (isset($_POST['boxIDs']) && \is_array($_POST['boxIDs'])) {
            $this->boxIDs = ArrayUtil::toIntegerArray($_POST['boxIDs']);
        }
        $box = Box::getBoxByIdentifier('com.woltlab.wcf.MainMenu');
        if (!\in_array($box->boxID, $this->boxIDs)) {
            $this->boxIDs[] = $box->boxID;
        }

        if (isset($_POST['aclValues']) && \is_array($_POST['aclValues'])) {
            $this->aclValues = $_POST['aclValues'];
        }
        if (isset($_POST['invertPermissions'])) {
            $this->invertPermissions = $_POST['invertPermissions'];
        }

        // If the page is allowed by all, the permission cannot be inverted.
        $outputAclValues = SimpleAclHandler::getInstance()->getOutputValues($this->aclValues);
        if ($outputAclValues['allowAll']) {
            $this->invertPermissions = 0;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->validateName();

        $this->validatePageType();

        $this->validateParentPageID();

        $this->validateApplicationPackageID();

        $this->validateParentMenuItemID();

        $this->validateCustomUrls();

        $this->validateTitle();

        $this->validateBoxIDs();

        if ($this->pageType === 'text') {
            if ($this->isMultilingual) {
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
                    $this->htmlInputProcessors[$language->languageID]->process(
                        (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
                        'com.woltlab.wcf.page.content'
                    );
                }
            } else {
                $this->htmlInputProcessors[0] = new HtmlInputProcessor();
                $this->htmlInputProcessors[0]->process(
                    (!empty($this->content[0]) ? $this->content[0] : ''),
                    'com.woltlab.wcf.page.content'
                );
            }
        }
    }

    /**
     * Validates page name.
     */
    protected function validateName()
    {
        if (empty($this->name)) {
            throw new UserInputException('name');
        }
        if (Page::getPageByName($this->name)) {
            throw new UserInputException('name', 'notUnique');
        }
    }

    /**
     * Validates page type.
     *
     * @throws  UserInputException
     */
    protected function validatePageType()
    {
        if (!\in_array($this->pageType, Page::$availablePageTypes) || $this->pageType == 'system') {
            throw new UserInputException('pageType');
        }

        if ($this->pageType == 'system' || \count($this->availableLanguages) === 1) {
            $this->isMultilingual = 0;
        }
    }

    /**
     * Validates parent page id.
     *
     * @throws  UserInputException
     */
    protected function validateParentPageID()
    {
        if ($this->parentPageID) {
            $page = new Page($this->parentPageID);
            if (!$page->pageID) {
                throw new UserInputException('parentPageID', 'invalid');
            }

            if ($page->requireObjectID) {
                throw new UserInputException('parentPageID', 'invalid');
            }
        }
    }

    /**
     * Validates package id.
     *
     * @throws  UserInputException
     */
    protected function validateApplicationPackageID()
    {
        if (!isset($this->availableApplications[$this->applicationPackageID])) {
            throw new UserInputException('applicationPackageID', 'invalid');
        }
    }

    /**
     * Validates custom urls.
     *
     * @throws  UserInputException
     */
    protected function validateCustomUrls()
    {
        if (empty($this->customURL) && $this->pageType != 'system') {
            if ($this->isMultilingual) {
                $language1 = \reset($this->availableLanguages);
                throw new UserInputException('customURL_' . $language1->languageID);
            } else {
                throw new UserInputException('customURL_0');
            }
        }

        if ($this->isMultilingual) {
            foreach ($this->availableLanguages as $language) {
                $this->validateCustomUrl($language->languageID, $this->customURL[$language->languageID] ?? '');
            }
        } else {
            $this->validateCustomUrl(0, $this->customURL[0] ?? '');
        }
    }

    /**
     * Validates given custom url.
     *
     * @param int $languageID
     * @param string $customURL
     *
     * @throws  UserInputException
     */
    protected function validateCustomUrl($languageID, $customURL)
    {
        if (empty($customURL)) {
            if ($this->pageType != 'system') {
                throw new UserInputException('customURL_' . $languageID);
            }
        } elseif (!RouteHandler::isValidCustomUrl($customURL)) {
            throw new UserInputException('customURL_' . $languageID, 'invalid');
        } else {
            // check whether url is already in use
            if (!PageEditor::isUniqueCustomUrl($customURL, $this->applicationPackageID)) {
                throw new UserInputException('customURL_' . $languageID, 'notUnique');
            }

            foreach ($this->customURL as $languageID2 => $customURL2) {
                if ($languageID != $languageID2 && $customURL == $customURL2) {
                    throw new UserInputException('customURL_' . $languageID, 'notUnique');
                }
            }
        }
    }

    /**
     * Replaces consecutive slashes with a single slash and trims any leading
     * or trailing slashes.
     *
     * @since 6.0
     */
    protected function normalizeCustomUrl(string $customUrl): string
    {
        $customUrl = \mb_strtolower($customUrl);
        $customUrl = \preg_replace('~/{2,}~', '/', $customUrl);

        return \trim($customUrl, '/');
    }

    /**
     * Validates page title.
     *
     * @throws UserInputException
     */
    protected function validateTitle()
    {
        if ($this->addPageToMainMenu) {
            if ($this->isMultilingual) {
                foreach ($this->availableLanguages as $language) {
                    if (empty($this->title[$language->languageID])) {
                        throw new UserInputException('title_' . $language->languageID);
                    }
                }
            } else {
                if (empty($this->title[0])) {
                    throw new UserInputException('title');
                }
            }
        }
    }

    /**
     * Validates parent menu item id.
     *
     * @throws  UserInputException
     */
    protected function validateParentMenuItemID()
    {
        if ($this->addPageToMainMenu && $this->parentMenuItemID) {
            $parentMenuItem = new MenuItem($this->parentMenuItemID);
            if (!$parentMenuItem->itemID || $parentMenuItem->menuID != MenuCache::getInstance()->getMainMenuID()) {
                throw new UserInputException('parentMenuItemID', 'invalid');
            }
        }
    }

    /**
     * Validates box ids.
     *
     * @throws  UserInputException
     */
    protected function validateBoxIDs()
    {
        foreach ($this->boxIDs as $boxID) {
            if (!isset($this->availableBoxes[$boxID])) {
                throw new UserInputException('boxIDs');
            }
        }
    }

    /**
     * Prepares box to page assignments
     *
     * @return  mixed[]
     */
    protected function getBoxToPage()
    {
        $boxToPage = [];
        foreach ($this->availableBoxes as $box) {
            if ($box->visibleEverywhere) {
                if (!\in_array($box->boxID, $this->boxIDs)) {
                    $boxToPage[] = [
                        'boxID' => $box->boxID,
                        'visible' => 0,
                    ];
                }
            } else {
                if (\in_array($box->boxID, $this->boxIDs)) {
                    $boxToPage[] = [
                        'boxID' => $box->boxID,
                        'visible' => 1,
                    ];
                }
            }
        }

        return $boxToPage;
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // prepare page content
        $content = [];
        if ($this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $content[$language->languageID] = [
                    'customURL' => !empty($this->customURL[$language->languageID]) ? $this->customURL[$language->languageID] : '',
                    'title' => !empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : '',
                    'content' => !empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : '',
                    'htmlInputProcessor' => $this->htmlInputProcessors[$language->languageID] ?? null,
                    'metaDescription' => !empty($this->metaDescription[$language->languageID]) ? $this->metaDescription[$language->languageID] : '',
                ];
            }
        } else {
            $content[0] = [
                'customURL' => !empty($this->customURL[0]) ? $this->customURL[0] : '',
                'title' => !empty($this->title[0]) ? $this->title[0] : '',
                'content' => !empty($this->content[0]) ? $this->content[0] : '',
                'htmlInputProcessor' => $this->htmlInputProcessors[0] ?? null,
                'metaDescription' => !empty($this->metaDescription[0]) ? $this->metaDescription[0] : '',
            ];
        }

        $this->objectAction = new PageAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'parentPageID' => $this->parentPageID ?: null,
                'pageType' => $this->pageType,
                'name' => $this->name,
                'cssClassName' => $this->cssClassName,
                'isDisabled' => $this->isDisabled ? 1 : 0,
                'availableDuringOfflineMode' => $this->availableDuringOfflineMode,
                'allowSpidersToIndex' => $this->allowSpidersToIndex,
                'enableShareButtons' => $this->enableShareButtons,
                'applicationPackageID' => $this->applicationPackageID,
                'lastUpdateTime' => TIME_NOW,
                'isMultilingual' => $this->isMultilingual,
                'identifier' => '',
                'packageID' => 1,
                'invertPermissions' => $this->invertPermissions,
            ]),
            'content' => $content,
            'boxToPage' => $this->getBoxToPage(),
        ]);

        /** @var Page $page */
        $page = $this->objectAction->executeAction()['returnValues'];

        // set generic page identifier
        $pageEditor = new PageEditor($page);
        $pageEditor->update([
            'identifier' => 'com.woltlab.wcf.generic' . $page->pageID,
        ]);

        // save acl
        SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.page', $page->pageID, $this->aclValues);

        // add page to main menu
        if ($this->addPageToMainMenu) {
            // select maximum showOrder value so that new menu item will be appened
            $conditionBuilder = new PreparedStatementConditionBuilder();
            if ($this->parentMenuItemID) {
                $conditionBuilder->add('parentItemID = ?', [$this->parentMenuItemID]);
            } else {
                $conditionBuilder->add('parentItemID IS NULL');
            }

            $sql = "SELECT  MAX(showOrder)
                    FROM    wcf" . WCF_N . "_menu_item
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $maxShowOrder = $statement->fetchSingleColumn() ?? 0;

            $menuItemAction = new MenuItemAction([], 'create', [
                'data' => [
                    'isDisabled' => $this->isDisabled ? 1 : 0,
                    'title' => (!$this->isMultilingual ? $this->title[0] : ''),
                    'pageID' => $page->pageID,
                    'menuID' => MenuCache::getInstance()->getMainMenuID(),
                    'parentItemID' => $this->parentMenuItemID,
                    'showOrder' => $maxShowOrder + 1,
                    'identifier' => StringUtil::getRandomID(),
                    'packageID' => 1,
                ],
            ]);
            $menuItemAction->executeAction();

            if ($this->isMultilingual) {
                $returnValues = $menuItemAction->getReturnValues();
                $menuItem = $returnValues['returnValues'];

                $data = ['identifier' => 'com.woltlab.wcf.generic' . $menuItem->itemID];
                $data['title'] = 'wcf.menu.item.' . $data['identifier'];
                I18nHandler::getInstance()->setValues('title', $this->title);
                I18nHandler::getInstance()->save('title', $data['title'], 'wcf.menu');

                $menuItemEditor = new MenuItemEditor($menuItem);
                $menuItemEditor->update($data);
            }
        }

        // call saved event
        $this->saved();

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                PageEditForm::class,
                ['id' => $page->pageID]
            ),
        ]);

        // reset variables
        $this->parentPageID = $this->isDisabled = $this->availableDuringOfflineMode = $this->enableShareButtons = $this->addPageToMainMenu = 0;
        $this->parentMenuItemID = null;
        $this->applicationPackageID = 1;
        $this->cssClassName = $this->name = '';
        $this->customURL = $this->title = $this->content = $this->metaDescription = $this->aclValues = [];
        $this->boxIDs = $this->getDefaultBoxIDs();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // set default values
        if (empty($_POST)) {
            $this->boxIDs = $this->getDefaultBoxIDs();

            if ($this->presetPage) {
                $this->name = $this->presetPage->name;
                $this->parentPageID = $this->presetPage->parentPageID;
                $this->pageType = $this->presetPage->pageType;
                $this->applicationPackageID = $this->presetPage->overrideApplicationPackageID ?: $this->presetPage->applicationPackageID;
                $this->cssClassName = $this->presetPage->cssClassName;
                if ($this->presetPage->controllerCustomURL) {
                    $this->customURL[0] = $this->presetPage->controllerCustomURL;
                }
                $this->isDisabled = 1;
                if ($this->presetPage->availableDuringOfflineMode) {
                    $this->availableDuringOfflineMode = 1;
                }
                if ($this->presetPage->allowSpidersToIndex) {
                    $this->allowSpidersToIndex = 1;
                } else {
                    $this->allowSpidersToIndex = 0;
                }
                $this->enableShareButtons = $this->presetPage->enableShareButtons;

                foreach ($this->presetPage->getPageContents() as $languageID => $content) {
                    $this->title[$languageID] = $content->title;
                    $this->content[$languageID] = $content->content;
                    $this->metaDescription[$languageID] = $content->metaDescription;
                    $this->customURL[$languageID] = $content->customURL;
                }

                $this->boxIDs = [];
                foreach ($this->availableBoxes as $box) {
                    if ($box->visibleEverywhere) {
                        if (!\in_array($box->boxID, $this->presetPage->getBoxIDs())) {
                            $this->boxIDs[] = $box->boxID;
                        }
                    } else {
                        if (\in_array($box->boxID, $this->presetPage->getBoxIDs())) {
                            $this->boxIDs[] = $box->boxID;
                        }
                    }
                }

                $this->aclValues = SimpleAclHandler::getInstance()->getValues(
                    'com.woltlab.wcf.page',
                    $this->presetPage->pageID
                );
                $this->invertPermissions = $this->presetPage->invertPermissions;
            }
        }

        $this->menuItems = new MenuItemNodeTree(MenuCache::getInstance()->getMainMenuID(), null, false);
    }

    /**
     * Returns the list of box ids that are enabled by default.
     *
     * @return      int[]
     */
    protected function getDefaultBoxIDs()
    {
        $boxIDs = [];
        foreach ($this->availableBoxes as $box) {
            if ($box->visibleEverywhere) {
                $boxIDs[] = $box->boxID;
            }
        }

        return $boxIDs;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        SmileyCache::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'parentPageID' => $this->parentPageID,
            'pageType' => $this->pageType,
            'name' => $this->name,
            'cssClassName' => $this->cssClassName,
            'isDisabled' => $this->isDisabled,
            'availableDuringOfflineMode' => $this->availableDuringOfflineMode,
            'allowSpidersToIndex' => $this->allowSpidersToIndex,
            'isMultilingual' => $this->isMultilingual,
            'applicationPackageID' => $this->applicationPackageID,
            'customURL' => $this->customURL,
            'title' => $this->title,
            'content' => $this->content,
            'metaDescription' => $this->metaDescription,
            /** @deprecated 5.4 - Meta keywords have been removed with 5.4. */
            'metaKeywords' => [],
            'boxIDs' => $this->boxIDs,
            'availableApplications' => $this->availableApplications,
            'availableLanguages' => $this->availableLanguages,
            'availableBoxes' => $this->availableBoxes,
            'pageNodeList' => (new PageNodeTree())->getNodeList(),
            'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->aclValues),
            'addPageToMainMenu' => $this->addPageToMainMenu,
            'parentMenuItemID' => $this->parentMenuItemID,
            'menuItemNodeList' => $this->menuItems->getNodeList(),
            'enableShareButtons' => $this->enableShareButtons,
            'invertPermissions' => $this->invertPermissions,
        ]);
    }
}
