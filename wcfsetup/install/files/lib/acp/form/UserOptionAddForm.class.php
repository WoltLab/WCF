<?php

namespace wcf\acp\form;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionAction;
use wcf\data\user\option\UserOptionEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\option\user\DateUserOptionOutput;
use wcf\system\option\user\LabeledUrlUserOptionOutput;
use wcf\system\option\user\SelectOptionsUserOptionOutput;
use wcf\system\option\user\URLUserOptionOutput;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user option add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * option name
     * @var string
     */
    public $optionName = '';

    /**
     * option description
     * @var string
     */
    public $optionDescription = '';

    /**
     * category name
     * @var string
     */
    public $categoryName = '';

    /**
     * option type
     * @var string
     */
    public $optionType = 'text';

    /**
     * option default value
     * @var string
     */
    public $defaultValue = '';

    /**
     * validation pattern
     * @var string
     */
    public $validationPattern = '';

    /**
     * select options
     * @var string
     */
    public $selectOptions = '';

    /**
     * @var string
     * @since 5.4
     */
    public $labeledUrl = '';

    /**
     * field is required
     * @var bool
     */
    public $required = 0;

    /**
     * shows this field in the registration process
     * @var bool
     */
    public $askDuringRegistration = 0;

    /**
     * edit permission bitmask
     * @var int
     */
    public $editable = 3;

    /**
     * view permission bitmask
     * @var int
     */
    public $visible = 15;

    /**
     * field is searchable
     * @var bool
     */
    public $searchable = 0;

    /**
     * show order
     * @var int
     */
    public $showOrder = 0;

    /**
     * output class
     * @var string
     */
    public $outputClass = '';

    /**
     * available option categories
     * @var UserOptionCategory[]
     */
    public $availableCategories = [];

    /**
     * valid editability bits for UserOptions
     * @var int[]
     */
    public $validEditableBits = [
        UserOption::EDITABILITY_NONE,
        UserOption::EDITABILITY_OWNER,
        UserOption::EDITABILITY_ADMINISTRATOR,
        UserOption::EDITABILITY_ALL,
        UserOption::EDITABILITY_OWNER_DURING_REGISTRATION_AND_ADMINISTRATOR,
    ];

    /**
     * available option types
     * @var string[]
     */
    public static $availableOptionTypes = [
        'birthday',
        'boolean',
        'checkboxes',
        'date',
        'integer',
        'float',
        'password',
        'multiSelect',
        'radioButton',
        'select',
        'text',
        'textarea',
        'message',
        'URL',
        'labeledUrl',
    ];

    /**
     * list of option type that require select options
     * @var string[]
     */
    public static $optionTypesUsingSelectOptions = [
        'checkboxes',
        'multiSelect',
        'radioButton',
        'select',
    ];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        I18nHandler::getInstance()->register('optionName');
        I18nHandler::getInstance()->register('optionDescription');

        // get available categories
        $categoryList = new UserOptionCategoryList();
        $categoryList->getConditionBuilder()->add('parentCategoryName = ?', ['profile']);
        $categoryList->readObjects();
        $this->availableCategories = $categoryList->getObjects();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        I18nHandler::getInstance()->readValues();

        if (I18nHandler::getInstance()->isPlainValue('optionName')) {
            $this->optionName = I18nHandler::getInstance()->getValue('optionName');
        }
        if (I18nHandler::getInstance()->isPlainValue('optionDescription')) {
            $this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
        }
        if (isset($_POST['categoryName'])) {
            $this->categoryName = $_POST['categoryName'];
        }
        if (isset($_POST['optionType'])) {
            $this->optionType = $_POST['optionType'];
        }
        if (isset($_POST['defaultValue'])) {
            $this->defaultValue = $_POST['defaultValue'];
        }
        if (isset($_POST['validationPattern'])) {
            $this->validationPattern = $_POST['validationPattern'];
        }
        if (isset($_POST['selectOptions'])) {
            $this->selectOptions = $_POST['selectOptions'];
        }
        if (isset($_POST['required'])) {
            $this->required = \intval($_POST['required']);
        }
        if (isset($_POST['askDuringRegistration'])) {
            $this->askDuringRegistration = \intval($_POST['askDuringRegistration']);
        }
        if (isset($_POST['editable'])) {
            $this->editable = \intval($_POST['editable']);
        }
        if (isset($_POST['visible'])) {
            $this->visible = \intval($_POST['visible']);
        }
        if (isset($_POST['searchable'])) {
            $this->searchable = \intval($_POST['searchable']);
        }
        if (isset($_POST['showOrder'])) {
            $this->showOrder = \intval($_POST['showOrder']);
        }
        if (isset($_POST['outputClass'])) {
            $this->outputClass = StringUtil::trim($_POST['outputClass']);
        }
        if (isset($_POST['labeledUrl'])) {
            $this->labeledUrl = StringUtil::trim($_POST['labeledUrl']);
        }

        if ($this->optionType == 'boolean' || $this->optionType == 'integer') {
            $this->defaultValue = \intval($this->defaultValue);
        }
        if ($this->optionType == 'float') {
            $this->defaultValue = \floatval($this->defaultValue);
        }
        if ($this->optionType == 'date') {
            if (!\preg_match('/\d{4}-\d{2}-\d{2}/', $this->defaultValue)) {
                $this->defaultValue = '';
            }
        }

        $this->setDefaultOutputClass();
    }

    /**
     * Sets the default output class.
     */
    protected function setDefaultOutputClass()
    {
        if (empty($this->outputClass)) {
            if (\in_array($this->optionType, self::$optionTypesUsingSelectOptions)) {
                $this->outputClass = SelectOptionsUserOptionOutput::class;
            }

            if ($this->optionType == 'date') {
                $this->outputClass = DateUserOptionOutput::class;
            }

            if ($this->optionType == 'URL') {
                $this->outputClass = URLUserOptionOutput::class;
            }

            if ($this->optionType == 'labeledUrl') {
                $this->outputClass = LabeledUrlUserOptionOutput::class;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // option name
        if (!I18nHandler::getInstance()->validateValue('optionName', true)) {
            throw new UserInputException('optionName', 'multilingual');
        }

        // category name
        if (empty($this->categoryName)) {
            throw new UserInputException('categoryName');
        }
        $sql = "SELECT  categoryID
                FROM    wcf1_user_option_category
                WHERE   categoryName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->categoryName]);
        if ($statement->fetchArray() === false) {
            throw new UserInputException('categoryName');
        }

        // option type
        if (!\in_array($this->optionType, self::$availableOptionTypes)) {
            throw new UserInputException('optionType');
        }

        // select options
        if (\in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
            throw new UserInputException('selectOptions');
        }

        if ($this->outputClass && !\class_exists($this->outputClass)) {
            throw new UserInputException('outputClass', 'doesNotExist');
        }

        if (!\in_array($this->editable, $this->validEditableBits)) {
            $this->editable = UserOption::EDITABILITY_ALL;
        }

        if ($this->optionType == 'labeledUrl' && \strpos($this->labeledUrl, '%s') === false) {
            throw new UserInputException('labeledUrl', 'invalid');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $additionalData = [];
        if ($this->optionType == 'select') {
            $additionalData['allowEmptyValue'] = true;
        }
        if ($this->optionType == 'message') {
            $additionalData['messageObjectType'] = 'com.woltlab.wcf.user.option.generic';
        }

        $this->objectAction = new UserOptionAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'optionName' => StringUtil::getRandomID(),
                'categoryName' => $this->categoryName,
                'optionType' => $this->optionType,
                'defaultValue' => $this->defaultValue,
                'showOrder' => $this->showOrder,
                'outputClass' => $this->outputClass,
                'validationPattern' => $this->validationPattern,
                'selectOptions' => $this->selectOptions,
                'required' => $this->required,
                'askDuringRegistration' => $this->askDuringRegistration,
                'searchable' => $this->searchable,
                'editable' => $this->editable,
                'visible' => $this->visible,
                'packageID' => 1,
                'additionalData' => !empty($additionalData) ? \serialize($additionalData) : '',
                'labeledUrl' => $this->labeledUrl,
            ]),
        ]);
        $this->objectAction->executeAction();

        $returnValues = $this->objectAction->getReturnValues();
        $userOption = $returnValues['returnValues'];

        // save language vars
        I18nHandler::getInstance()->save(
            'optionName',
            'wcf.user.option.option' . $userOption->optionID,
            'wcf.user.option'
        );
        I18nHandler::getInstance()->save(
            'optionDescription',
            'wcf.user.option.option' . $userOption->optionID . '.description',
            'wcf.user.option'
        );
        $editor = new UserOptionEditor($userOption);
        $editor->update([
            'optionName' => 'option' . $userOption->optionID,
        ]);
        $this->saved();

        // reset values
        $this->optionName = $this->optionDescription = $this->categoryName = $this->optionType = $this->defaultValue = $this->validationPattern = $this->selectOptions = $this->outputClass = '';
        $this->optionType = 'text';
        $this->required = $this->searchable = $this->showOrder = $this->askDuringRegistration = 0;
        $this->editable = 3;
        $this->visible = 15;

        I18nHandler::getInstance()->reset();

        // show success
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                UserOptionEditForm::class,
                ['id' => $userOption->optionID]
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'optionName' => $this->optionName,
            'optionDescription' => $this->optionDescription,
            'categoryName' => $this->categoryName,
            'optionType' => $this->optionType,
            'defaultValue' => $this->defaultValue,
            'validationPattern' => $this->validationPattern,
            'selectOptions' => $this->selectOptions,
            'required' => $this->required,
            'askDuringRegistration' => $this->askDuringRegistration,
            'editable' => $this->editable,
            'visible' => $this->visible,
            'searchable' => $this->searchable,
            'showOrder' => $this->showOrder,
            'outputClass' => $this->outputClass,
            'action' => 'add',
            'availableCategories' => $this->availableCategories,
            'availableOptionTypes' => self::$availableOptionTypes,
            'labeledUrl' => $this->labeledUrl,
        ]);
    }
}
