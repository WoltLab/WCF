<?php

namespace wcf\system\package\plugin;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionEditor;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\option\user\IUserOptionOutput;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes user options.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $className = UserOptionEditor::class;

    /**
     * @inheritDoc
     */
    public $tableName = 'user_option';

    /**
     * @inheritDoc
     */
    public $tagName = 'option';

    /**
     * list of names of tags which aren't considered as additional data
     * @var string[]
     */
    public static $reservedTags = [
        'name',
        'optiontype',
        'defaultvalue',
        'validationpattern',
        'required',
        'editable',
        'visible',
        'searchable',
        'showorder',
        'outputclass',
        'selectoptions',
        'enableoptions',
        'isdisabled',
        'categoryname',
        'permissions',
        'options',
        'attrs',
        'cdata',
    ];

    /**
     * @inheritDoc
     */
    protected function saveCategory($category, $categoryXML = null)
    {
        // use for create and update
        $data = [
            'parentCategoryName' => $category['parentCategoryName'] ?? '',
            'permissions' => $category['permissions'] ?? '',
            'options' => $category['options'] ?? '',
        ];
        // append show order if explicitly stated
        if (isset($category['showOrder'])) {
            $data['showOrder'] = $category['showOrder'];
        }

        $userOptionCategory = UserOptionCategory::getCategoryByName($category['categoryName']);
        if ($userOptionCategory !== null) {
            if ($userOptionCategory->packageID != $this->installation->getPackageID()) {
                throw new SystemException("Cannot override existing category '" . $category['categoryName'] . "'");
            }

            $categoryEditor = new UserOptionCategoryEditor($userOptionCategory);
            $categoryEditor->update($data);
        } else {
            // append data fields for create
            $data['packageID'] = $this->installation->getPackageID();
            $data['categoryName'] = $category['categoryName'];

            UserOptionCategoryEditor::create($data);
        }
    }

    /**
     * @inheritDoc
     */
    protected function saveOption($option, $categoryName, $existingOptionID = 0)
    {
        // default values
        $optionName = $optionType = $validationPattern = $outputClass = $selectOptions = $enableOptions = $permissions = $options = '';
        $required = $editable = $visible = $searchable = $isDisabled = $askDuringRegistration = 0;
        $defaultValue = $showOrder = null;

        // get values
        if (isset($option['name'])) {
            $optionName = $option['name'];
        }
        if (isset($option['optiontype'])) {
            $optionType = $option['optiontype'];
        }
        if (isset($option['defaultvalue'])) {
            $defaultValue = $option['defaultvalue'];
        }
        if (isset($option['validationpattern'])) {
            $validationPattern = $option['validationpattern'];
        }
        if (isset($option['required'])) {
            $required = \intval($option['required']);
        }
        if (isset($option['askduringregistration'])) {
            $askDuringRegistration = \intval($option['askduringregistration']);
        }
        if (isset($option['editable'])) {
            $editable = \intval($option['editable']);
        }
        if (isset($option['visible'])) {
            $visible = \intval($option['visible']);
        }
        if (isset($option['searchable'])) {
            $searchable = \intval($option['searchable']);
        }
        if (isset($option['showorder'])) {
            $showOrder = \intval($option['showorder']);
        }
        if (isset($option['outputclass'])) {
            $outputClass = $option['outputclass'];
        }
        if (isset($option['selectoptions'])) {
            $selectOptions = $option['selectoptions'];
        }
        if (isset($option['enableoptions'])) {
            $enableOptions = StringUtil::normalizeCsv($option['enableoptions']);
        }
        if (isset($option['isdisabled'])) {
            $isDisabled = \intval($option['isdisabled']);
        }
        $showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
        if (isset($option['permissions'])) {
            $permissions = StringUtil::normalizeCsv($option['permissions']);
        }
        if (isset($option['options'])) {
            $options = StringUtil::normalizeCsv($option['options']);
        }

        // collect additional tags and their values
        $additionalData = [];
        foreach ($option as $tag => $value) {
            if (!\in_array($tag, self::$reservedTags)) {
                $additionalData[$tag] = $value;
            }
        }

        // get optionID if it was installed by this package already
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   optionName = ?
                    AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $optionName,
            $this->installation->getPackageID(),
        ]);
        $result = $statement->fetchArray();

        // build data array
        $data = [
            'categoryName' => $categoryName,
            'optionType' => $optionType,
            'defaultValue' => $defaultValue,
            'validationPattern' => $validationPattern,
            'selectOptions' => $selectOptions,
            'enableOptions' => $enableOptions,
            'required' => $required,
            'askDuringRegistration' => $askDuringRegistration,
            'editable' => $editable,
            'visible' => $visible,
            'outputClass' => $outputClass,
            'searchable' => $searchable,
            'showOrder' => $showOrder,
            'permissions' => $permissions,
            'options' => $options,
            'additionalData' => \serialize($additionalData),
            'originIsSystem' => 1,
        ];

        // update option
        if (!empty($result['optionID']) && $this->installation->getAction() == 'update') {
            $userOption = new UserOption(null, $result);
            $userOptionEditor = new UserOptionEditor($userOption);
            $userOptionEditor->update($data);
        } // insert new option
        else {
            // append option name
            $data['optionName'] = $optionName;
            // append disabled state
            $data['isDisabled'] = $isDisabled;

            // create option
            $data['packageID'] = $this->installation->getPackageID();
            UserOptionEditor::create($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // get optionsIDs from package
        $sql = "SELECT  optionID
                FROM    wcf1_user_option
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
        while ($row = $statement->fetchArray()) {
            WCF::getDB()->getEditor()->dropColumn(
                'wcf1_user_option_value',
                'userOption' . $row['optionID']
            );
        }

        // uninstall options and categories
        parent::uninstall();
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        parent::addFormFields($form);

        if ($this->entryType === 'options') {
            // add `hidden` pseudo-category
            /** @var SingleSelectionFormField $categoryName */
            $categoryName = $form->getNodeById('categoryName');
            $options = $categoryName->getNestedOptions();
            $options[] = [
                'depth' => 0,
                'label' => 'hidden',
                'value' => 'hidden',
            ];
            $categoryName->options($options, true);

            /** @var IFormContainer $dataContainer */
            $dataContainer = $form->getNodeById('data');

            /** @var SingleSelectionFormField $optionType */
            $optionType = $form->getNodeById('optionType');

            $selectOptions = MultilineTextFormField::create('selectOptions')
                ->objectProperty('selectoptions')
                ->label('wcf.acp.pip.abstractOption.options.selectOptions')
                ->description('wcf.acp.pip.abstractOption.options.selectOptions.description')
                ->rows(5)
                ->addDependency(
                    ValueFormFieldDependency::create('optionType')
                        ->field($optionType)
                        ->values($this->selectOptionOptionTypes)
                );

            $dataContainer->insertBefore($selectOptions, 'enableOptions');

            $dataContainer->appendChildren([
                BooleanFormField::create('required')
                    ->label('wcf.acp.pip.userOption.options.required')
                    ->description('wcf.acp.pip.userOption.options.required.description'),

                BooleanFormField::create('askDuringRegistration')
                    ->objectProperty('askduringregistration')
                    ->label('wcf.acp.pip.userOption.options.askDuringRegistration')
                    ->description('wcf.acp.pip.userOption.options.askDuringRegistration.description'),

                SingleSelectionFormField::create('editable')
                    ->label('wcf.acp.pip.userOption.options.editable')
                    ->description('wcf.acp.pip.userOption.options.editable.description')
                    ->options([
                        0 => 'wcf.acp.user.option.editable.0',
                        1 => 'wcf.acp.user.option.editable.1',
                        2 => 'wcf.acp.user.option.editable.2',
                        3 => 'wcf.acp.user.option.editable.3',
                        6 => 'wcf.acp.user.option.editable.6',
                    ]),

                SingleSelectionFormField::create('visible')
                    ->label('wcf.acp.pip.userOption.options.visible')
                    ->description('wcf.acp.pip.userOption.options.visible.description')
                    ->options([
                        0 => 'wcf.acp.user.option.visible.0',
                        1 => 'wcf.acp.user.option.visible.1',
                        2 => 'wcf.acp.user.option.visible.2',
                        3 => 'wcf.acp.user.option.visible.3',
                        7 => 'wcf.acp.user.option.visible.7',
                        15 => 'wcf.acp.user.option.visible.15',
                    ]),

                ClassNameFormField::create('outputClass')
                    ->objectProperty('outputclass')
                    ->label('wcf.acp.pip.userOption.options.outputClass')
                    ->implementedInterface(IUserOptionOutput::class),

                BooleanFormField::create('searchable')
                    ->label('wcf.acp.pip.userOption.options.searchable')
                    ->description('wcf.acp.pip.userOption.options.searchable.description'),

                BooleanFormField::create('isDisabled')
                    ->objectProperty('isdisabled')
                    ->label('wcf.acp.pip.userOption.options.isDisabled')
                    ->description('wcf.acp.pip.userOption.options.isDisabled.description'),

                // option type-specific fields
                SingleSelectionFormField::create('messageObjectType')
                    ->label('wcf.acp.pip.userOption.options.messageObjectType')
                    ->description('wcf.acp.pip.userOption.options.messageObjectType.description')
                    ->options(static function () {
                        $options = [];
                        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message');
                        foreach ($objectTypes as $objectType) {
                            $options[$objectType->objectType] = $objectType->objectType;
                        }

                        \asort($options);

                        return $options;
                    }, false, false)
                    ->addDependency(
                        ValueFormFieldDependency::create('optionType')
                            ->field($optionType)
                            ->values(['aboutMe', 'message'])
                    ),

                TextFormField::create('contentPattern')
                    ->objectProperty('contentpattern')
                    ->label('wcf.acp.pip.userOption.options.contentPattern')
                    ->description('wcf.acp.pip.userOption.options.contentPattern.description')
                    ->addValidator(new FormFieldValidator('regex', static function (TextFormField $formField) {
                        if ($formField->getSaveValue() !== '') {
                            if (!Regex::compile($formField->getSaveValue())->isValid()) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'invalid',
                                        'wcf.acp.pip.userOption.options.contentPattern.error.invalid'
                                    )
                                );
                            }
                        }
                    }))
                    ->addDependency(
                        ValueFormFieldDependency::create('optionType')
                            ->field($optionType)
                            ->values(['text'])
                    ),
            ]);

            // ensure proper normalization of select options
            $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                'selectOptions',
                static function (IFormDocument $document, array $parameters) {
                    if (isset($parameters['data']['selectoptions'])) {
                        $parameters['data']['selectoptions'] = StringUtil::unifyNewlines(
                            $parameters['data']['selectoptions']
                        );
                    }

                    return $parameters;
                }
            ));
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = parent::fetchElementData($element, $saveData);

        switch ($this->entryType) {
            case 'options':
                $optionals = [
                    'required',
                    'askDuringRegistration',
                    'editable',
                    'visible',
                    'outputClass',
                    'searchable',
                    'isDisabled',
                    'contentPattern',
                    'selectOptions',
                ];

                foreach ($optionals as $optionalPropertyName) {
                    $optionalProperty = $element->getElementsByTagName(\strtolower($optionalPropertyName))->item(0);
                    if ($optionalProperty !== null) {
                        if ($saveData) {
                            $data[\strtolower($optionalPropertyName)] = $optionalProperty->nodeValue;
                        } else {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        }
                    } elseif ($saveData && $optionalPropertyName === 'selectOptions') {
                        // all of the other fields will be put in `additionalData`,
                        // thus empty values are not necessary
                        $data['selectoptions'] = '';
                    }
                }

                $messageObjectType = $element->getElementsByTagName('messageObjectType')->item(0);
                if ($messageObjectType !== null) {
                    $data['messageObjectType'] = $messageObjectType->nodeValue;
                }

                break;
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getSortOptionHandler()
    {
        // reuse UserGroupOptionHandler
        return new class(true) extends UserOptionHandler {
            /**
             * @inheritDoc
             */
            protected function checkCategory(OptionCategory $category)
            {
                // we do not care for category checks here
                return true;
            }

            /**
             * @inheritDoc
             */
            protected function checkOption(Option $option)
            {
                // we do not care for option checks here
                return true;
            }

            /**
             * @inheritDoc
             */
            public function getCategoryOptions($categoryName = '', $inherit = true)
            {
                // we just need to ensure that the category is not empty
                return [new Option(null, [])];
            }
        };
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $option = parent::prepareXmlElement($document, $form);

        switch ($this->entryType) {
            case 'options':
                $this->appendElementChildren(
                    $option,
                    [
                        'selectoptions' => '',
                        'outputclass' => '',
                        'required' => 0,
                        'askduringregistration' => 0,
                        'searchable' => 0,
                        'visible' => '0',
                        'editable' => '0',
                        'isdisabled' => 0,
                        'messageObjectType' => '',
                        'contentpattern' => '',
                    ],
                    $form
                );

                break;
        }

        return $option;
    }
}
