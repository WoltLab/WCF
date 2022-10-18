<?php

namespace wcf\acp\form;

use wcf\data\label\group\LabelGroupList;
use wcf\data\label\LabelAction;
use wcf\data\label\LabelEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the label add form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
class LabelAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

    /**
     * label group id
     * @var int
     */
    public $groupID = 0;

    /**
     * label value
     * @var string
     */
    public $label = '';

    /**
     * label group list object
     * @var LabelGroupList
     */
    public $labelGroupList;

    /**
     * CSS class name
     * @var string
     */
    public $cssClassName = '';

    /**
     * custom CSS class name
     * @var string
     */
    public $customCssClassName = '';

    /**
     * list of pre-defined css class names
     * @var string[]
     */
    public $availableCssClassNames = [
        'yellow',
        'orange',
        'brown',
        'red',
        'pink',
        'purple',
        'blue',
        'green',
        'black',

        'none', /* not a real value */
        'custom', /* not a real value */
    ];

    /**
     * show order
     * @var int
     */
    public $showOrder = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        I18nHandler::getInstance()->register('label');
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        I18nHandler::getInstance()->readValues();

        if (I18nHandler::getInstance()->isPlainValue('label')) {
            $this->label = I18nHandler::getInstance()->getValue('label');
        }
        if (isset($_POST['cssClassName'])) {
            $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
        }
        if (isset($_POST['customCssClassName'])) {
            $this->customCssClassName = StringUtil::trim($_POST['customCssClassName']);
        }
        if (isset($_POST['groupID'])) {
            $this->groupID = (int)$_POST['groupID'];
        }
        if (isset($_POST['showOrder'])) {
            $this->showOrder = (int)$_POST['showOrder'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->validateGroup();

        // validate label
        if (!I18nHandler::getInstance()->validateValue('label')) {
            if (I18nHandler::getInstance()->isPlainValue('label')) {
                throw new UserInputException('label');
            } else {
                throw new UserInputException('label', 'multilingual');
            }
        }

        // validate class name
        if (empty($this->cssClassName)) {
            throw new UserInputException('cssClassName', 'empty');
        } elseif (!\in_array($this->cssClassName, $this->availableCssClassNames)) {
            throw new UserInputException('cssClassName', 'invalid');
        } elseif ($this->cssClassName == 'custom') {
            if (
                !empty($this->customCssClassName)
                && !Regex::compile('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$')->match($this->customCssClassName)
            ) {
                throw new UserInputException('cssClassName', 'invalid');
            }
        }

        if ($this->showOrder < 0) {
            $this->showOrder = 0;
        }
    }

    /**
     * Validates selected label group.
     *
     * @throws UserInputException
     */
    protected function validateGroup()
    {
        // validate group
        if (!$this->groupID) {
            throw new UserInputException('groupID');
        }
        $groups = $this->labelGroupList->getObjects();
        if (!isset($groups[$this->groupID])) {
            throw new UserInputException('groupID', 'invalid');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // save label
        $this->objectAction = new LabelAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'label' => $this->label,
                'cssClassName' => $this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName,
                'groupID' => $this->groupID,
                'showOrder' => $this->showOrder,
            ]),
        ]);
        $this->objectAction->executeAction();
        $returnValues = $this->objectAction->getReturnValues();
        $labelID = $returnValues['returnValues']->labelID;

        if (!I18nHandler::getInstance()->isPlainValue('label')) {
            I18nHandler::getInstance()->save('label', 'wcf.acp.label.label' . $labelID, 'wcf.acp.label', 1);

            // update group name
            $labelEditor = new LabelEditor($returnValues['returnValues']);
            $labelEditor->update([
                'label' => 'wcf.acp.label.label' . $labelID,
            ]);
        }

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType');
        foreach ($objectTypes as $objectType) {
            $objectType->getProcessor()->save();
        }

        $this->saved();

        // reset values
        $this->label = $this->cssClassName = $this->customCssClassName = '';
        $this->groupID = $this->showOrder = 0;

        I18nHandler::getInstance()->reset();

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(LabelEditForm::class, ['id' => $labelID]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $this->labelGroupList = new LabelGroupList();
        $this->labelGroupList->readObjects();

        parent::readData();
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
            'availableCssClassNames' => $this->availableCssClassNames,
            'cssClassName' => $this->cssClassName,
            'customCssClassName' => $this->customCssClassName,
            'groupID' => $this->groupID,
            'label' => $this->label,
            'labelGroupList' => $this->labelGroupList,
            'showOrder' => $this->showOrder,
        ]);
    }
}
