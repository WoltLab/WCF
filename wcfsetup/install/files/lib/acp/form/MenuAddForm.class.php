<?php

namespace wcf\acp\form;

use wcf\data\box\Box;
use wcf\data\menu\MenuAction;
use wcf\data\menu\MenuEditor;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the menu add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MenuAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMenu'];

    /**
     * menu title
     * @var string
     */
    public $title = '';

    /**
     * box position
     * @var string
     */
    public $position = '';

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
     * page ids
     * @var int[]
     */
    public $pageIDs = [];

    /**
     * acl values
     * @var array
     */
    public $aclValues = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        I18nHandler::getInstance()->register('title');
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        I18nHandler::getInstance()->readValues();

        if (I18nHandler::getInstance()->isPlainValue('title')) {
            $this->title = I18nHandler::getInstance()->getValue('title');
        }

        $this->visibleEverywhere = $this->showHeader = $this->showOrder = 0;
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
        if (isset($_POST['pageIDs']) && \is_array($_POST['pageIDs'])) {
            $this->pageIDs = ArrayUtil::toIntegerArray($_POST['pageIDs']);
        }
        if (isset($_POST['aclValues']) && \is_array($_POST['aclValues'])) {
            $this->aclValues = $_POST['aclValues'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // validate menu title
        if (!I18nHandler::getInstance()->validateValue('title')) {
            if (I18nHandler::getInstance()->isPlainValue('title')) {
                throw new UserInputException('title');
            } else {
                throw new UserInputException('title', 'multilingual');
            }
        }

        // validate box position
        $this->validatePosition();

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
    }

    /**
     * Validates box position.
     *
     * @throws  UserInputException
     */
    protected function validatePosition()
    {
        if (!\in_array($this->position, Box::$availablePositions)) {
            throw new UserInputException('position');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $boxName = $this->title;
        if (!I18nHandler::getInstance()->isPlainValue('title')) {
            $values = I18nHandler::getInstance()->getValues('title');
            $boxName = $values[LanguageFactory::getInstance()->getDefaultLanguageID()];
        }

        // save label
        $this->objectAction = new MenuAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'title' => $this->title,
                'packageID' => 1,
                'identifier' => '',
            ]),
            'boxData' => [
                'name' => $boxName,
                'boxType' => 'menu',
                'position' => $this->position,
                'visibleEverywhere' => $this->visibleEverywhere ? 1 : 0,
                'showHeader' => $this->showHeader ? 1 : 0,
                'showOrder' => $this->showOrder,
                'cssClassName' => $this->cssClassName,
                'packageID' => 1,
            ],
            'pageIDs' => $this->pageIDs,
        ]);
        $returnValues = $this->objectAction->executeAction();
        // set generic identifier
        $menuEditor = new MenuEditor($returnValues['returnValues']);
        $menuEditor->update([
            'identifier' => 'com.woltlab.wcf.genericMenu' . $menuEditor->menuID,
        ]);
        // save i18n
        if (!I18nHandler::getInstance()->isPlainValue('title')) {
            I18nHandler::getInstance()->save(
                'title',
                'wcf.menu.com.woltlab.wcf.genericMenu' . $menuEditor->menuID,
                'wcf.menu',
                1
            );

            // update title
            $menuEditor->update([
                'title' => 'wcf.menu.com.woltlab.wcf.genericMenu' . $menuEditor->menuID,
            ]);
        }

        // save acl
        SimpleAclHandler::getInstance()->setValues(
            'com.woltlab.wcf.box',
            $menuEditor->getDecoratedObject()->getBox()->boxID,
            $this->aclValues
        );

        $this->saved();

        // reset values
        $this->cssClassName = $this->title = '';
        $this->position = 'contentTop';
        $this->showOrder = 0;
        $this->visibleEverywhere = $this->showHeader = 1;
        $this->pageIDs = $this->aclValues = [];

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                MenuEditForm::class,
                ['id' => $menuEditor->menuID]
            ),
        ]);

        I18nHandler::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'title' => 'title',
            'position' => $this->position,
            'cssClassName' => $this->cssClassName,
            'showOrder' => $this->showOrder,
            'visibleEverywhere' => $this->visibleEverywhere,
            'showHeader' => $this->showHeader,
            'pageIDs' => $this->pageIDs,
            'availablePositions' => Box::$availableMenuPositions,
            'pageNodeList' => (new PageNodeTree())->getNodeList(),
            'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->aclValues),
        ]);
    }
}
