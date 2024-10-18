<?php

namespace wcf\acp\form;

use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\data\media\Media;
use wcf\data\media\ViewableMediaList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\page\PageNodeTree;
use wcf\data\smiley\SmileyCache;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\box\IBoxController;
use wcf\system\box\IConditionBoxController;
use wcf\system\condition\ConditionHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the box add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class BoxAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.box.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageBox'];

    /**
     * true if created box is multi-lingual
     * @var bool
     */
    public $isMultilingual = 0;

    /**
     * box type
     * @var string
     */
    public $boxType = '';

    /**
     * box position
     * @var string
     */
    public $position = 'contentTop';

    /**
     * show order
     * @var int
     */
    public $showOrder = 0;

    /**
     * true if created box is visible everywhere
     * @var bool
     */
    public $visibleEverywhere = 1;

    /**
     * css class name of created box
     * @var string
     */
    public $cssClassName = '';

    /**
     * true if box header is visible
     * @var bool
     */
    public $showHeader = 1;

    /**
     * box name
     * @var string
     */
    public $name = '';

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
     * image ids
     * @var int[]
     */
    public $imageID = [];

    /**
     * images
     * @var Media[]
     */
    public $images = [];

    /**
     * page ids
     * @var int[]
     */
    public $pageIDs = [];

    /**
     * object type id of the selected box controller
     * @var int
     */
    public $boxControllerID = 0;

    /**
     * selected box controller object type
     * @var ObjectType
     */
    public $boxController;

    /**
     * link type
     * @var string
     */
    public $linkType = 'none';

    /**
     * link page id
     * @var int
     */
    public $linkPageID = 0;

    /**
     * link page object id
     * @var int
     */
    public $linkPageObjectID = 0;

    /**
     * link external URL
     * @var string
     */
    public $externalURL = '';

    /**
     * list of page handlers by page id
     * @var IMenuPageHandler[]
     */
    public $pageHandlers = [];

    /**
     * nested list of page nodes
     * @var \RecursiveIteratorIterator
     */
    public $pageNodeList;

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
     * @var ObjectType[]
     */
    public $availableBoxControllers = [];

    /**
     * list of available positions per box handler
     * @var array
     */
    public $availableBoxPositions = [];

    /**
     * @var bool
     * @since   5.2
     */
    public $isDisabled = 0;

    /**
     * @var int
     * @since   5.2
     */
    public $presetBoxID = 0;

    /**
     * @var Box
     * @since   5.2
     */
    public $presetBox;

    /**
     * @var bool
     * @since   5.4
     */
    public $invertPermissions;

    /**
     * grouped boxes condition object types
     * @var ObjectType[][]
     */
    public $groupedConditionObjectTypes = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['presetBoxID'])) {
            $this->presetBoxID = \intval($_GET['presetBoxID']);
        }
        if ($this->presetBoxID) {
            $this->presetBox = new Box($this->presetBoxID);
            if (!$this->presetBox->boxID) {
                throw new IllegalLinkException();
            }
        }

        $this->readBoxType();

        $this->pageNodeList = (new PageNodeTree())->getNodeList();

        // fetch page handlers
        foreach ($this->pageNodeList as $pageNode) {
            $handler = $pageNode->getHandler();
            if ($handler !== null) {
                if ($handler instanceof ILookupPageHandler) {
                    $this->pageHandlers[$pageNode->pageID] = $pageNode->requireObjectID;
                }
            }
        }

        $this->availableBoxControllers = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.boxController');
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $this->availableBoxControllers,
            static fn (ObjectType $a, ObjectType $b) => $collator->compare(
                WCF::getLanguage()->get('wcf.acp.box.boxController.' . $a->objectType),
                WCF::getLanguage()->get('wcf.acp.box.boxController.' . $b->objectType)
            )
        );

        $this->readBoxPositions();
    }

    /**
     * Loads available box positions per box controller.
     */
    protected function readBoxPositions()
    {
        foreach ($this->availableBoxControllers as $boxController) {
            /** @var IBoxController $controller */
            $controller = $boxController->getProcessor();
            $this->availableBoxPositions[$boxController->objectTypeID] = $controller::getSupportedPositions();
        }
    }

    /**
     * Reads basic page parameters controlling type and i18n.
     *
     * @throws  IllegalLinkException
     */
    protected function readBoxType()
    {
        if ($this->presetBox) {
            $this->isMultilingual = $this->presetBox->isMultilingual;
            $this->boxType = $this->presetBox->boxType;

            return;
        }

        if (!empty($_REQUEST['isMultilingual'])) {
            $this->isMultilingual = 1;
        }
        if (!empty($_REQUEST['boxType'])) {
            $this->boxType = $_REQUEST['boxType'];
        }

        // work-around to force adding boxes via dialog overlay
        if (empty($_POST) && $this->boxType == '') {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('BoxList', ['showBoxAddDialog' => 1]));

            exit;
        }

        // validate box type
        if (!\in_array($this->boxType, Box::$availableBoxTypes)) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->visibleEverywhere = $this->showHeader = $this->showOrder = 0;
        if (isset($_POST['name'])) {
            $this->name = StringUtil::trim($_POST['name']);
        }
        if (isset($_POST['boxType'])) {
            $this->boxType = $_POST['boxType'];
        }
        if (isset($_POST['position'])) {
            $this->position = $_POST['position'];
        }
        if (isset($_POST['showOrder'])) {
            $this->showOrder = \intval($_POST['showOrder']);
        }
        if (isset($_POST['visibleEverywhere'])) {
            $this->visibleEverywhere = \intval($_POST['visibleEverywhere']);
        }
        if (isset($_POST['cssClassName'])) {
            $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
        }
        if (isset($_POST['showHeader'])) {
            $this->showHeader = \intval($_POST['showHeader']);
        }
        if (isset($_POST['isDisabled'])) {
            $this->isDisabled = 1;
        }
        if (isset($_POST['pageIDs']) && \is_array($_POST['pageIDs'])) {
            $this->pageIDs = ArrayUtil::toIntegerArray($_POST['pageIDs']);
        }
        if (isset($_POST['linkType'])) {
            $this->linkType = $_POST['linkType'];
        }
        if (!empty($_POST['linkPageID'])) {
            $this->linkPageID = \intval($_POST['linkPageID']);
        }
        if (!empty($_POST['linkPageObjectID'])) {
            $this->linkPageObjectID = \intval($_POST['linkPageObjectID']);
        }
        if (isset($_POST['externalURL'])) {
            $this->externalURL = StringUtil::trim($_POST['externalURL']);
        }
        if (isset($_POST['title']) && \is_array($_POST['title'])) {
            $this->title = ArrayUtil::trim($_POST['title']);
        }
        if (isset($_POST['content']) && \is_array($_POST['content'])) {
            $this->content = ArrayUtil::trim($_POST['content']);
        }
        if (isset($_POST['boxControllerID'])) {
            $this->boxControllerID = \intval($_POST['boxControllerID']);
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

        if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
            if (isset($_POST['imageID']) && \is_array($_POST['imageID'])) {
                $this->imageID = ArrayUtil::toIntegerArray($_POST['imageID']);
            }

            $this->readBoxImages();
        }

        if ($this->boxType === 'system') {
            $this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->boxControllerID);
        }

        $this->readConditions();
    }

    private function readConditions(): void
    {
        foreach ($this->toFlatList($this->groupedConditionObjectTypes) as $objectType) {
            $objectType->getProcessor()->readFormParameters();
        }
    }

    /**
     * Reads the box images.
     */
    protected function readBoxImages()
    {
        if (!empty($this->imageID)) {
            $mediaList = new ViewableMediaList();
            $mediaList->setObjectIDs($this->imageID);
            $mediaList->readObjects();

            foreach ($this->imageID as $languageID => $imageID) {
                $image = $mediaList->search($imageID);
                if ($image !== null && $image->isImage) {
                    $this->images[$languageID] = $image;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // validate name
        $this->validateName();

        // validate controller
        if ($this->boxType === 'system') {
            if ($this->boxController === null || $this->boxController->getDefinition()->definitionName != 'com.woltlab.wcf.boxController') {
                throw new UserInputException('boxController');
            }

            if ($this->boxController && $this->boxController->getProcessor() instanceof IConditionBoxController) {
                $this->boxController->getProcessor()->readConditions();
            }
        } else {
            $this->boxControllerID = 0;
        }

        // validate box position
        $this->validateBoxPosition();

        // validate link
        if ($this->boxType !== 'system' && $this->linkType == 'internal') {
            $this->externalURL = '';

            if (!$this->linkPageID) {
                throw new UserInputException('linkPageID');
            }
            $page = new Page($this->linkPageID);
            if (!$page->pageID) {
                throw new UserInputException('linkPageID', 'invalid');
            }

            // validate page object id
            if (isset($this->pageHandlers[$page->pageID])) {
                if ($this->pageHandlers[$page->pageID] && !$this->linkPageObjectID) {
                    throw new UserInputException('linkPageObjectID');
                }

                /** @var ILookupPageHandler $handler */
                $handler = $page->getHandler();
                if ($this->linkPageObjectID && !$handler->isValid($this->linkPageObjectID)) {
                    throw new UserInputException('linkPageObjectID', 'invalid');
                }
            }
        } elseif ($this->boxType !== 'system' && $this->linkType == 'external') {
            $this->linkPageID = $this->linkPageObjectID = null;

            if (empty($this->externalURL)) {
                throw new UserInputException('externalURL');
            }
        } else {
            $this->linkPageID = $this->linkPageObjectID = null;
            $this->externalURL = '';
        }

        if ($this->boxController && $this->boxController->getProcessor() instanceof IConditionBoxController) {
            $this->boxController->getProcessor()->validateConditions();
        }

        // validate images
        if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
            foreach ($this->imageID as $languageID => $imageID) {
                if (!isset($this->imageID[$languageID])) {
                    throw new UserInputException('imageID' . $languageID);
                }
            }
        }

        // box title
        if ($this->showHeader) {
            if ($this->boxType == 'system' || $this->isMultilingual) {
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    if (empty($this->title[$language->languageID])) {
                        throw new UserInputException('title' . $language->languageID);
                    }
                }
            } else {
                if (empty($this->title[0])) {
                    throw new UserInputException('title');
                }
            }
        }

        if ($this->boxType == 'text') {
            if ($this->isMultilingual) {
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
                    $this->htmlInputProcessors[$language->languageID]->process(
                        (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
                        'com.woltlab.wcf.box.content'
                    );
                }
            } else {
                $this->htmlInputProcessors[0] = new HtmlInputProcessor();
                $this->htmlInputProcessors[0]->process(
                    (!empty($this->content[0]) ? $this->content[0] : ''),
                    'com.woltlab.wcf.box.content'
                );
            }
        }

        // validate page ids
        if (!empty($this->pageIDs)) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('pageID IN (?)', [$this->pageIDs]);
            $sql = "SELECT  pageID
                    FROM    wcf1_page
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
            $this->pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        }

        foreach ($this->toFlatList($this->groupedConditionObjectTypes) as $objectType) {
            $objectType->getProcessor()->validate();
        }
    }

    /**
     * Validates box name.
     */
    protected function validateName()
    {
        if (empty($this->name)) {
            throw new UserInputException('name');
        }
        if (Box::getBoxByName($this->name)) {
            throw new UserInputException('name', 'notUnique');
        }
    }

    /**
     * Validates the selected box position.
     */
    protected function validateBoxPosition()
    {
        if (!\in_array($this->position, Box::$availablePositions)) {
            throw new UserInputException('position');
        }

        if ($this->boxType == 'system') {
            if (!\in_array($this->position, $this->availableBoxPositions[$this->boxController->objectTypeID])) {
                throw new UserInputException('position', 'invalid');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $content = [];
        if ($this->boxType == 'system' || $this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $content[$language->languageID] = [
                    'title' => !empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : '',
                    'content' => !empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : '',
                    'htmlInputProcessor' => $this->htmlInputProcessors[$language->languageID] ?? null,
                    'imageID' => !empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null,
                ];
            }
        } else {
            $content[0] = [
                'title' => !empty($this->title[0]) ? $this->title[0] : '',
                'content' => !empty($this->content[0]) ? $this->content[0] : '',
                'htmlInputProcessor' => $this->htmlInputProcessors[0] ?? null,
                'imageID' => !empty($this->imageID[0]) ? $this->imageID[0] : null,
            ];
        }

        $data = [
            'name' => $this->name,
            'packageID' => 1,
            'isMultilingual' => $this->isMultilingual,
            'boxType' => $this->boxType,
            'position' => $this->position,
            'showOrder' => $this->showOrder,
            'visibleEverywhere' => $this->visibleEverywhere,
            'lastUpdateTime' => TIME_NOW,
            'cssClassName' => $this->cssClassName,
            'showHeader' => $this->showHeader,
            'isDisabled' => $this->isDisabled ? 1 : 0,
            'linkPageID' => $this->linkPageID,
            'linkPageObjectID' => $this->linkPageObjectID ?: 0,
            'externalURL' => $this->externalURL,
            'identifier' => '',
            'invertPermissions' => $this->invertPermissions,
        ];
        if ($this->boxControllerID) {
            $data['objectTypeID'] = $this->boxControllerID;
        }

        $this->objectAction = new BoxAction([], 'create', [
            'data' => \array_merge($this->additionalFields, $data),
            'content' => $content,
            'pageIDs' => $this->pageIDs,
        ]);
        $box = $this->objectAction->executeAction()['returnValues'];

        // set generic box identifier
        $boxEditor = new BoxEditor($box);
        $boxEditor->update([
            'identifier' => 'com.woltlab.wcf.genericBox' . $boxEditor->boxID,
        ]);

        if ($this->boxController) {
            if ($this->boxController->getProcessor() instanceof IConditionBoxController) {
                $this->boxController->getProcessor()->setBox($box, false);
            } else {
                $this->boxController->getProcessor()->setBox($box);
            }

            $this->boxController->getProcessor()->saveAdditionalData();
        }

        // save acl
        SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.box', $box->boxID, $this->aclValues);

        ConditionHandler::getInstance()->createConditions(
            $box->boxID,
            $this->toFlatList($this->groupedConditionObjectTypes)
        );

        // call saved event
        $this->saved();

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                BoxEditForm::class,
                ['id' => $box->getObjectID()]
            ),
        ]);

        // reset variables
        $this->cssClassName = $this->name = '';
        $this->position = 'contentTop';
        $this->showOrder = $this->boxControllerID = $this->isDisabled = $this->invertPermissions = 0;
        $this->visibleEverywhere = $this->showHeader = 1;
        $this->title = $this->content = $this->images = $this->imageID = $this->pageIDs = $this->aclValues = [];
        $this->boxController = null;
        $this->linkType = 'none';
        $this->linkPageID = 0;
        $this->linkPageObjectID = 0;

        foreach ($this->toFlatList($this->groupedConditionObjectTypes) as $condition) {
            $condition->getProcessor()->reset();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes(Box::VISIBILITY_CONDITIONS_OBJECT_TYPE_NAME);
        foreach ($objectTypes as $objectType) {
            if (!$objectType->conditionobject) {
                continue;
            }

            if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject])) {
                $this->groupedConditionObjectTypes[$objectType->conditionobject] = [];
            }

            if ($objectType->conditiongroup) {
                if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup])) {
                    $this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup] = [];
                }

                $this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
            } else {
                $this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->objectTypeID] = $objectType;
            }
        }

        parent::readData();

        if (empty($_POST) && $this->presetBox) {
            $this->name = $this->presetBox->name;
            $this->boxType = $this->presetBox->boxType;
            $this->position = $this->presetBox->position;
            $this->showOrder = $this->presetBox->showOrder;
            $this->cssClassName = $this->presetBox->cssClassName;
            $this->boxControllerID = $this->presetBox->objectTypeID;
            if ($this->presetBox->showHeader) {
                $this->showHeader = 1;
            } else {
                $this->showHeader = 0;
            }
            $this->isDisabled = 1;
            if ($this->presetBox->visibleEverywhere) {
                $this->visibleEverywhere = 1;
            } else {
                $this->visibleEverywhere = 0;
            }
            $this->pageIDs = $this->presetBox->getPageIDs();
            $this->linkPageID = $this->presetBox->linkPageID;
            $this->linkPageObjectID = $this->presetBox->linkPageObjectID;
            $this->externalURL = $this->presetBox->externalURL;
            if ($this->linkPageID) {
                $this->linkType = 'internal';
            }
            if ($this->externalURL) {
                $this->linkType = 'external';
            }

            foreach ($this->presetBox->getBoxContents() as $languageID => $content) {
                $this->title[$languageID] = $content->title;
                $this->content[$languageID] = $content->content;
                $this->imageID[$languageID] = $content->imageID;
            }

            if ($this->boxControllerID) {
                $this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->boxControllerID);
                if ($this->boxController->getProcessor() instanceof IConditionBoxController) {
                    $this->boxController->getProcessor()->setBox($this->presetBox);
                }
            }

            $this->invertPermissions = $this->presetBox->invertPermissions;
            $this->aclValues = SimpleAclHandler::getInstance()->getValues(
                'com.woltlab.wcf.box',
                $this->presetBox->boxID
            );

            $this->readBoxImages();

            $conditions = $this->presetBox->getVisibilityConditions();
            $conditionsByObjectTypeID = [];
            foreach ($conditions as $condition) {
                $conditionsByObjectTypeID[$condition->objectTypeID] = $condition;
            }

            foreach ($this->toFlatList($this->groupedConditionObjectTypes) as $objectType) {
                if (isset($conditionsByObjectTypeID[$objectType->objectTypeID])) {
                    $conditionsByObjectTypeID[$objectType->objectTypeID]->getObjectType()->getProcessor()->setData($conditionsByObjectTypeID[$objectType->objectTypeID]);
                }
            }
        }
    }

    /**
     * This is a helper method to convert groupedConditionObjectTypes to a flat list.
     * This method should not be used for any other purpose!
     *
     * @since 5.5
     */
    protected function toFlatList(array $array): array
    {
        $returnList = [];

        foreach ($array as $element) {
            if (\is_array($element)) {
                $returnList = \array_merge($returnList, $this->toFlatList($element));
            } else {
                $returnList[] = $element;
            }
        }

        return $returnList;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        SmileyCache::getInstance()->assignVariables();

        if ($this->boxType == 'text') {
            if ($this->isMultilingual) {
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $upcastProcessor = new HtmlUpcastProcessor();
                    $upcastProcessor->process(
                        $this->content[$language->languageID] ?? '',
                        'com.woltlab.wcf.box.content'
                    );
                    $this->content[$language->languageID] = $upcastProcessor->getHtml();
                }
            } else {
                $upcastProcessor = new HtmlUpcastProcessor();
                $upcastProcessor->process($this->content[0] ?? '', 'com.woltlab.wcf.box.content');
                $this->content[0] = $upcastProcessor->getHtml();
            }
        }

        WCF::getTPL()->assign([
            'action' => 'add',
            'isMultilingual' => $this->isMultilingual,
            'name' => $this->name,
            'boxType' => $this->boxType,
            'position' => $this->position,
            'cssClassName' => $this->cssClassName,
            'showOrder' => $this->showOrder,
            'visibleEverywhere' => $this->visibleEverywhere,
            'showHeader' => $this->showHeader,
            'isDisabled' => $this->isDisabled,
            'title' => $this->title,
            'content' => $this->content,
            'imageID' => $this->imageID,
            'images' => $this->images,
            'pageIDs' => $this->pageIDs,
            'linkType' => $this->linkType,
            'linkPageID' => $this->linkPageID,
            'linkPageObjectID' => $this->linkPageObjectID,
            'externalURL' => $this->externalURL,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'availableBoxTypes' => Box::$availableBoxTypes,
            'availablePositions' => Box::$availablePositions,
            'availableBoxControllers' => $this->availableBoxControllers,
            'boxController' => $this->boxController,
            'pageNodeList' => $this->pageNodeList,
            'pageHandlers' => $this->pageHandlers,
            'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->aclValues),
            'availableBoxPositions' => $this->availableBoxPositions,
            'invertPermissions' => $this->invertPermissions,
            'groupedConditionObjectTypes' => $this->groupedConditionObjectTypes,
        ]);
    }
}
