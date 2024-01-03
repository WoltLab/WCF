<?php

namespace wcf\acp\form;

use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageCache;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Shows the page add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class PageEditForm extends PageAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';

    /**
     * @var int
     */
    public $overrideApplicationPackageID;

    /**
     * page id
     * @var int
     */
    public $pageID = 0;

    /**
     * page object
     * @var Page
     */
    public $page;

    /**
     * @inheritDoc
     *
     * @throws  IllegalLinkException
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->pageID = \intval($_REQUEST['id']);
        }
        $this->page = new Page($this->pageID);
        if (!$this->page->pageID) {
            throw new IllegalLinkException();
        }
        if ($this->page->isMultilingual) {
            $this->isMultilingual = 1;
        }
    }

    /**
     * @inheritDoc
     */
    protected function readPageType()
    {
        // not required for editing
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['overrideApplicationPackageID'])) {
            $this->overrideApplicationPackageID = \intval($_POST['overrideApplicationPackageID']);
        }

        $this->pageType = $this->page->pageType;
        if ($this->page->originIsSystem) {
            $this->applicationPackageID = $this->page->applicationPackageID;

            if ($this->page->hasFixedParent) {
                $this->parentPageID = $this->page->parentPageID;
            }
        } else {
            $this->overrideApplicationPackageID = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->validateOverrideApplicationPackageID();
    }

    protected function validateOverrideApplicationPackageID()
    {
        if ($this->overrideApplicationPackageID) {
            if ($this->overrideApplicationPackageID == $this->applicationPackageID) {
                // Picking the same app would have the same result, but also creates some overhead in the internal routing.
                $this->overrideApplicationPackageID = null;
            } elseif (ApplicationHandler::getInstance()->getApplicationByID($this->overrideApplicationPackageID) === null) {
                throw new UserInputException('overrideApplicationPackageID', 'invalid');
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateName()
    {
        if (\mb_strtolower($this->name) != \mb_strtolower($this->page->name)) {
            parent::validateName();
        }
    }

    /**
     * @inheritDoc
     */
    protected function validatePageType()
    {
        // type is immutable
    }

    /**
     * @inheritDoc
     */
    protected function validateParentPageID()
    {
        if ($this->page->hasFixedParent) {
            if ($this->parentPageID != $this->page->parentPageID) {
                throw new UserInputException('parentPageID', 'invalid');
            }
        } else {
            parent::validateParentPageID();

            if ($this->parentPageID) {
                if ($this->parentPageID == $this->pageID) {
                    throw new UserInputException('parentPageID', 'invalid');
                }

                $page = PageCache::getInstance()->getPage($this->parentPageID);
                while ($page->parentPageID !== null) {
                    $page = PageCache::getInstance()->getPage($page->parentPageID);
                    if ($page->pageID == $this->pageID) {
                        throw new UserInputException('parentPageID', 'invalid');
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateCustomUrl($languageID, $customURL)
    {
        if ($this->pageType == 'system') {
            if ($customURL != $this->page->controllerCustomURL) {
                parent::validateCustomUrl($languageID, $customURL);
            }
        } else {
            if (isset($this->page->getPageContents()[$languageID]) && \mb_strtolower($customURL) != \mb_strtolower($this->page->getPageContents()[$languageID]->customURL)) {
                parent::validateCustomUrl($languageID, $customURL);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $data = [
            'name' => $this->name,
            'cssClassName' => $this->cssClassName,
            'isDisabled' => $this->isDisabled ? 1 : 0,
            'lastUpdateTime' => TIME_NOW,
            'parentPageID' => $this->parentPageID ?: null,
            'applicationPackageID' => $this->applicationPackageID,
            'overrideApplicationPackageID' => $this->overrideApplicationPackageID,
            'availableDuringOfflineMode' => $this->availableDuringOfflineMode,
            'allowSpidersToIndex' => $this->allowSpidersToIndex,
            'enableShareButtons' => $this->enableShareButtons,
            'invertPermissions' => $this->invertPermissions,
        ];

        if ($this->pageType == 'system') {
            $content = [];
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $content[$language->languageID] = [
                    'customURL' => '',
                    'title' => !empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : '',
                    'content' => '',
                    'metaDescription' => !empty($this->metaDescription[$language->languageID]) ? $this->metaDescription[$language->languageID] : '',
                ];
            }

            $data['controllerCustomURL'] = (!empty($this->customURL[0]) ? $this->customURL[0] : '');
            $this->objectAction = new PageAction([$this->page], 'update', [
                'data' => \array_merge($this->additionalFields, $data),
                'boxToPage' => $this->getBoxToPage(),
                'content' => $content,
            ]);
            $this->objectAction->executeAction();
        } else {
            $content = [];
            if ($this->page->isMultilingual) {
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

            $this->objectAction = new PageAction([$this->page], 'update', [
                'data' => \array_merge($this->additionalFields, $data),
                'content' => $content,
                'boxToPage' => $this->getBoxToPage(),
            ]);
            $this->objectAction->executeAction();
        }

        // save acl
        if ($this->page->pageType != 'system') {
            SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.page', $this->page->pageID, $this->aclValues);
        }

        // call saved event
        $this->saved();

        // Ensure that the CKEditor has the correct content after save.
        if ($this->pageType == 'text') {
            if ($this->isMultilingual) {
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $this->content[$language->languageID] = isset($this->htmlInputProcessors[$language->languageID]) ?
                    $this->htmlInputProcessors[$language->languageID]->getHtml() : '';
                }
            } else {
                $this->content[0] = isset($this->htmlInputProcessors[0]) ?
                    $this->htmlInputProcessors[0]->getHtml() : '';
            }
        }

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            $this->name = $this->page->name;
            $this->parentPageID = $this->page->parentPageID;
            $this->pageType = $this->page->pageType;
            $this->applicationPackageID = $this->page->applicationPackageID;
            $this->overrideApplicationPackageID = $this->page->overrideApplicationPackageID;
            $this->cssClassName = $this->page->cssClassName;
            if ($this->page->controllerCustomURL) {
                $this->customURL[0] = $this->page->controllerCustomURL;
            }
            if ($this->page->isDisabled) {
                $this->isDisabled = 1;
            }
            if ($this->page->availableDuringOfflineMode) {
                $this->availableDuringOfflineMode = 1;
            }
            if ($this->page->allowSpidersToIndex) {
                $this->allowSpidersToIndex = 1;
            } else {
                $this->allowSpidersToIndex = 0;
            }
            $this->enableShareButtons = $this->page->enableShareButtons;

            foreach ($this->page->getPageContents() as $languageID => $content) {
                $this->title[$languageID] = $content->title;
                $this->content[$languageID] = $content->content;
                $this->metaDescription[$languageID] = $content->metaDescription;
                $this->customURL[$languageID] = $content->customURL;
            }

            $this->boxIDs = [];
            foreach ($this->availableBoxes as $box) {
                if ($box->visibleEverywhere) {
                    if (!\in_array($box->boxID, $this->page->getBoxIDs())) {
                        $this->boxIDs[] = $box->boxID;
                    }
                } else {
                    if (\in_array($box->boxID, $this->page->getBoxIDs())) {
                        $this->boxIDs[] = $box->boxID;
                    }
                }
            }

            $this->aclValues = SimpleAclHandler::getInstance()->getValues('com.woltlab.wcf.page', $this->page->pageID);
            $this->invertPermissions = $this->page->invertPermissions;
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit',
            'pageID' => $this->pageID,
            'page' => $this->page,
            'lastVersion' => VersionTracker::getInstance()->getLastVersion('com.woltlab.wcf.page', $this->pageID),
            'overrideApplicationPackageID' => $this->overrideApplicationPackageID,
        ]);
    }
}
