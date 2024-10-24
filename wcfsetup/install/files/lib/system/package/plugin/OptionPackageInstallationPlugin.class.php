<?php

namespace wcf\system\package\plugin;

use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\option\OptionEditor;
use wcf\data\option\OptionList;
use wcf\data\package\Package;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\option\OptionHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes options.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class OptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $className = OptionEditor::class;

    /**
     * @inheritDoc
     */
    public $tableName = 'option';

    /**
     * list of names of tags which aren't considered as additional data
     * @var string[]
     */
    public static $reservedTags = [
        'name',
        'optiontype',
        'defaultvalue',
        'validationpattern',
        'enableoptions',
        'showorder',
        'hidden',
        'selectoptions',
        'categoryname',
        'permissions',
        'options',
        'attrs',
        'cdata',
        'supporti18n',
        'requirei18n',
    ];

    /**
     * @inheritDoc
     */
    protected function saveOption($option, $categoryName, $existingOptionID = 0)
    {
        // default values
        $optionName = $optionType = $defaultValue = $validationPattern = $selectOptions = $enableOptions = $permissions = $options = '';
        $showOrder = null;
        $hidden = $supportI18n = $requireI18n = 0;

        // get values
        if (isset($option['name'])) {
            $optionName = $option['name'];
        }
        if (isset($option['optiontype'])) {
            $optionType = $option['optiontype'];
        }
        if (isset($option['defaultvalue'])) {
            $defaultValue = WCF::getLanguage()->get($option['defaultvalue']);
        }
        if (isset($option['validationpattern'])) {
            $validationPattern = $option['validationpattern'];
        }
        if (isset($option['enableoptions'])) {
            $enableOptions = StringUtil::normalizeCsv($option['enableoptions']);
        }
        if (isset($option['showorder'])) {
            $showOrder = \intval($option['showorder']);
        }
        if (isset($option['hidden'])) {
            $hidden = \intval($option['hidden']);
        }
        $showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
        if (isset($option['selectoptions'])) {
            $selectOptions = $option['selectoptions'];
        }
        if (isset($option['permissions'])) {
            $permissions = StringUtil::normalizeCsv($option['permissions']);
        }
        if (isset($option['options'])) {
            $options = StringUtil::normalizeCsv($option['options']);
        }
        if (isset($option['supporti18n'])) {
            $supportI18n = $option['supporti18n'];
        }
        if (isset($option['requirei18n'])) {
            $requireI18n = $option['requirei18n'];
        }

        // collect additional tags and their values
        $additionalData = [];
        foreach ($option as $tag => $value) {
            if (!\in_array($tag, self::$reservedTags)) {
                $additionalData[$tag] = $value;
            }
        }

        // build update or create data
        $data = [
            'categoryName' => $categoryName,
            'optionType' => $optionType,
            'validationPattern' => $validationPattern,
            'selectOptions' => $selectOptions,
            'showOrder' => $showOrder,
            'enableOptions' => $enableOptions,
            'hidden' => $hidden,
            'permissions' => $permissions,
            'options' => $options,
            'supportI18n' => $supportI18n,
            'requireI18n' => $requireI18n,
            'additionalData' => \serialize($additionalData),
        ];

        // try to find an existing option for updating
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $optionName,
        ]);
        $row = $statement->fetchArray();

        // result was 'false' thus create a new item
        if (!$row) {
            // set the value of 'app_install_date' to the current timestamp
            if ($hidden && $optionType == 'integer' && $this->installation->getPackage()->isApplication) {
                $abbreviation = Package::getAbbreviation($this->installation->getPackage()->package);
                if ($optionName == $abbreviation . '_install_date') {
                    $defaultValue = TIME_NOW;
                }
            }

            $data['optionName'] = $optionName;
            $data['packageID'] = $this->installation->getPackageID();
            $data['optionValue'] = $defaultValue;

            OptionEditor::create($data);
        } else {
            // editing an option from a different package
            if ($row['packageID'] != $this->installation->getPackageID()) {
                throw new SystemException("Option '" . $optionName . "' already exists, but is owned by a different package");
            }

            // update existing item
            $optionObj = new Option(null, $row);
            $optionEditor = new OptionEditor($optionObj);
            $optionEditor->update($data);
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        parent::addFormFields($form);

        /** @var IFormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        switch ($this->entryType) {
            case 'options':
                /** @var SingleSelectionFormField $optionType */
                $optionType = $form->getNodeById('optionType');

                /** @var TextFormField $optionNameField */
                $optionNameField = $dataContainer->getNodeById('optionName');
                $optionNameField->addValidator(new FormFieldValidator(
                    'uniqueness',
                    function (TextFormField $formField) {
                        if (
                            $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                            || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                        ) {
                            $optionList = new OptionList();
                            $optionList->getConditionBuilder()->add('optionName = ?', [$formField->getValue()]);
                            $optionList->getConditionBuilder()->add('packageID IN (?)', [
                                \array_merge(
                                    [$this->installation->getPackage()->packageID],
                                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                                ),
                            ]);

                            if ($optionList->countObjects() > 0) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'notUnique',
                                        'wcf.acp.pip.option.optionName.error.notUnique'
                                    )
                                );
                            }
                        }
                    }
                ));
                $optionNameField->addValidator(new FormFieldValidator(
                    'lowercase',
                    static function (TextFormField $formField) {
                        $value = $formField->getValue();
                        if ($value !== \strtolower($value)) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notLowercase',
                                    'wcf.acp.pip.option.optionName.error.notLowercase'
                                )
                            );
                        }
                    }
                ));
                $optionNameField->addValidator(new FormFieldValidator(
                    'pattern',
                    static function (TextFormField $formField) {
                        $value = $formField->getValue();
                        if (!\preg_match('/^[a-z][a-z0-9_]*[a-z]$/', $value)) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'pattern',
                                    'wcf.acp.pip.option.optionName.error.pattern'
                                )
                            );
                        }
                    }
                ));

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

                $selectOptions = MultilineTextFormField::create('selectOptions')
                    ->objectProperty('selectoptions')
                    ->label('wcf.acp.pip.abstractOption.options.selectOptions')
                    ->description('wcf.acp.pip.option.abstractOption.selectOptions.description')
                    ->rows(5)
                    ->addDependency(
                        ValueFormFieldDependency::create('optionType')
                            ->field($optionType)
                            ->values($this->selectOptionOptionTypes)
                    );

                $dataContainer->insertBefore($selectOptions, 'enableOptions');

                $dataContainer->appendChildren([
                    BooleanFormField::create('hidden')
                        ->label('wcf.acp.pip.option.options.hidden')
                        ->description('wcf.acp.pip.option.options.hidden.description'),

                    BooleanFormField::create('supportI18n')
                        ->objectProperty('supporti18n')
                        ->label('wcf.acp.pip.option.options.supportI18n')
                        ->description('wcf.acp.pip.option.options.supportI18n.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values($this->i18nOptionTypes)
                        ),

                    BooleanFormField::create('requireI18n')
                        ->objectProperty('requirei18n')
                        ->label('wcf.acp.pip.option.options.requireI18n')
                        ->description('wcf.acp.pip.option.options.requireI18n.description')
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('supportI18n')
                                ->fieldId('supportI18n')
                        ),
                ]);

                // ensure proper normalization of select options
                $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                    'selectOptions',
                    static function (IFormDocument $document, array $parameters) {
                        if (isset($parameters['data']['selectoptions'])) {
                            $parameters['data']['selectoptions'] = StringUtil::unifyNewlines($parameters['data']['selectoptions']);
                        }

                        return $parameters;
                    }
                ));

                break;
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
                foreach (['selectOptions', 'hidden', 'supportI18n', 'requireI18n'] as $optionalPropertyName) {
                    $optionalProperty = $element->getElementsByTagName(\strtolower($optionalPropertyName))->item(0);
                    if ($optionalProperty !== null) {
                        $data[$saveData ? \strtolower($optionalPropertyName) : $optionalPropertyName] = $optionalProperty->nodeValue;
                    } elseif ($saveData) {
                        if ($optionalPropertyName === 'selectOptions') {
                            $data['selectoptions'] = '';
                        } else {
                            $data[\strtolower($optionalPropertyName)] = 0;
                        }
                    }
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
        // reuse OptionHandler
        return new class(true) extends OptionHandler {
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
                        'hidden' => 0,
                        'supporti18n' => 0,
                        'requirei18n' => 0,
                    ],
                    $form
                );

                break;
        }

        return $option;
    }
}
