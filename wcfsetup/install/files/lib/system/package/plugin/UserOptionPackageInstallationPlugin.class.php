<?php

namespace wcf\system\package\plugin;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\option\category\OptionCategory;
use wcf\data\package\Package;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryBuilder;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\data\user\option\UserOptionEditor;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Installs, updates and deletes user options and user option categories.
 *
 * @author      Alexander Ebert, Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IIdempotentPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin {
        addDeleteElement as defaultAddDeleteElement;
        sanitizeXmlFileAfterDeleteEntry as defaultSanitizeXmlFileAfterDeleteEntry;
    }

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
        'askduringregistration',
        // no longer supported, ignored for backwards compatibility
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

    #[\Override]
    public function install()
    {
        AbstractPackageInstallationPlugin::install();

        $xml = $this->getXML($this->instruction['value']);
        $xpath = $xml->xpath();

        if ($this->installation->getAction() === 'update') {
            $this->deleteItems($xpath);
        }

        $this->importCategories($xpath);
        $this->importOptions($xpath);

        UserOptionBuilder::resetCache();

        return null;
    }

    #[\Override]
    protected function deleteItems(\DOMXPath $xpath)
    {
        $optionNames = [];
        foreach ($xpath->query('/ns:data/ns:delete/ns:option') as $element) {
            \assert($element instanceof \DOMElement);
            $optionNames[] = $element->getAttribute('name');
        }
        if ($optionNames !== []) {
            $this->deleteOptions('optionName', $optionNames);
        }

        $categoryNames = [];
        foreach ($xpath->query('/ns:data/ns:delete/ns:optioncategory') as $element) {
            \assert($element instanceof \DOMElement);
            $categoryNames[] = $element->getAttribute('name');
        }
        if ($categoryNames !== []) {
            $this->deleteCategories($categoryNames);
        }
    }

    /**
     * Deletes the options of this package whose value of the given column is
     * one of the given values, including their value columns.
     *
     * @param string[] $values
     */
    private function deleteOptions(string $columnName, array $values): void
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("{$columnName} IN (?)", [$values]);
        $conditions->add('packageID = ?', [$this->installation->getPackageID()]);

        $sql = "SELECT  optionID
                FROM    wcf1_user_option
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $optionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($optionIDs !== []) {
            UserOptionBuilder::deleteAll($optionIDs);
        }
    }

    /**
     * Deletes the given categories of this package including their options.
     *
     * @param string[] $categoryNames
     */
    private function deleteCategories(array $categoryNames): void
    {
        $this->deleteOptions('categoryName', $categoryNames);

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('categoryName IN (?)', [$categoryNames]);
        $conditions->add('packageID = ?', [$this->installation->getPackageID()]);

        $sql = "SELECT  categoryID
                FROM    wcf1_user_option_category
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $categoryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($categoryIDs !== []) {
            UserOptionCategoryBuilder::deleteAll($categoryIDs);
        }
    }

    /**
     * Imports the option categories.
     *
     * @throws SystemException
     */
    protected function importCategories(\DOMXPath $xpath): void
    {
        foreach ($xpath->query('/ns:data/ns:import/ns:categories/ns:category') as $element) {
            \assert($element instanceof \DOMElement);

            $data = [];
            foreach ($xpath->query('child::*', $element) as $child) {
                \assert($child instanceof \DOMElement);
                $data[$child->tagName] = $child->nodeValue;
            }

            $category = [
                'categoryName' => $element->getAttribute('name'),
                'options' => isset($data['options']) ? StringUtil::normalizeCsv($data['options']) : '',
                'parentCategoryName' => $data['parent'] ?? '',
                'permissions' => isset($data['permissions']) ? StringUtil::normalizeCsv($data['permissions']) : '',
                'showOrder' => isset($data['showorder']) ? (int)$data['showorder'] : null,
            ];

            if (
                $category['showOrder'] !== null
                || $this->installation->getAction() !== 'update'
                || UserOptionCategory::getCategoryByName($category['categoryName']) === null
            ) {
                $category['showOrder'] = $this->getShowOrder(
                    $category['showOrder'],
                    $category['parentCategoryName'],
                    'parentCategoryName',
                    '_category'
                );
            }

            if (
                $category['parentCategoryName'] !== ''
                && UserOptionCategory::getCategoryByName($category['parentCategoryName']) === null
            ) {
                throw new SystemException(
                    "Unable to find parent 'option category' with name '{$category['parentCategoryName']}' for category with name '{$category['categoryName']}'."
                );
            }

            $this->saveCategory($category);
        }
    }

    /**
     * Imports the options.
     */
    protected function importOptions(\DOMXPath $xpath): void
    {
        foreach ($xpath->query('/ns:data/ns:import/ns:options/ns:option') as $element) {
            \assert($element instanceof \DOMElement);

            $data = [];
            foreach ($xpath->query('child::*', $element) as $child) {
                \assert($child instanceof \DOMElement);
                $data[$child->tagName] = $child->nodeValue;
            }
            $data['name'] = $element->getAttribute('name');

            $this->validateOption($data['name']);
            $this->saveOption($data, $data['categoryname']);
        }
    }

    /**
     * @throws SystemException if the name is invalid or the option is provided by another package
     */
    protected function validateOption(string $optionName): void
    {
        if (!\preg_match('/^[\w\-\.]+$/', $optionName)) {
            $matches = [];
            \preg_match_all('/(\W)/', $optionName, $matches);

            throw new SystemException(
                "The option '{$optionName}' has at least one non-alphanumeric character (underscore is permitted): ("
                    . \implode("), ( ", $matches[1]) . ")."
            );
        }

        $sql = "SELECT  packageID
                FROM    wcf1_user_option
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$optionName]);
        $packageID = $statement->fetchSingleColumn();

        if ($packageID !== false && $packageID !== $this->installation->getPackageID()) {
            $package = new Package($packageID);

            throw new SystemException(
                "user_option '{$optionName}' is already provided by '{$package}' ('{$package->package}')."
            );
        }
    }

    /**
     * Saves the given option category.
     *
     * @param array{
     *  categoryName: string,
     *  parentCategoryName?: string,
     *  permissions?: string,
     *  options?: string,
     *  showOrder?: ?int,
     * } $category
     * @throws SystemException if the category is provided by another package
     */
    protected function saveCategory(array $category): void
    {
        $userOptionCategory = UserOptionCategory::getCategoryByName($category['categoryName']);
        if ($userOptionCategory !== null) {
            if ($userOptionCategory->packageID !== $this->installation->getPackageID()) {
                throw new SystemException("Cannot override existing category '" . $category['categoryName'] . "'");
            }

            $builder = UserOptionCategoryBuilder::forUpdate($userOptionCategory);
        } else {
            $builder = UserOptionCategoryBuilder::forCreate()
                ->setPackageID($this->installation->getPackageID())
                ->setCategoryName($category['categoryName']);
        }

        $builder
            ->setParentCategoryName($category['parentCategoryName'] ?? '')
            ->setPermissions($category['permissions'] ?? '')
            ->setOptions($category['options'] ?? '');
        if (isset($category['showOrder'])) {
            $builder->setShowOrder($category['showOrder']);
        }

        if ($builder->isUpdate()) {
            $builder->update();
        } else {
            $builder->create();
        }
    }

    /**
     * Saves the given option, the keys of the data are the names of the XML elements.
     *
     * @param array<string, int|string|null> $option
     */
    protected function saveOption(array $option, string $categoryName): void
    {
        $optionName = (string)($option['name'] ?? '');

        // Legacy option types with an equivalent form option are converted.
        $optionType = (string)($option['optiontype'] ?? '');
        $optionType = UserOption::LEGACY_OPTION_TYPES[$optionType] ?? $optionType;

        $showOrder = isset($option['showorder']) ? (int)$option['showorder'] : null;
        $showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');

        // collect additional tags and their values
        $additionalData = [];
        foreach ($option as $tag => $value) {
            if (!\in_array($tag, self::$reservedTags, true)) {
                $additionalData[$tag] = $value;
            }
        }

        $existingOption = $this->getExistingOption($optionName);
        if ($existingOption !== null && $this->installation->getAction() === 'update') {
            $builder = UserOptionBuilder::forUpdate($existingOption);
        } else {
            $builder = UserOptionBuilder::forCreate()
                ->setPackageID($this->installation->getPackageID())
                ->setOptionName($optionName)
                ->setIsDisabled((bool)($option['isdisabled'] ?? false));
        }

        $builder
            ->setCategoryName($categoryName)
            ->setOptionType($optionType)
            ->setDefaultValue(isset($option['defaultvalue']) ? (string)$option['defaultvalue'] : null)
            ->setValidationPattern((string)($option['validationpattern'] ?? ''))
            ->setSelectOptions((string)($option['selectoptions'] ?? ''))
            ->setEnableOptions(StringUtil::normalizeCsv((string)($option['enableoptions'] ?? '')))
            ->setEditable((int)($option['editable'] ?? 0))
            ->setVisible((int)($option['visible'] ?? 0))
            ->setShowOrder($showOrder)
            ->setPermissions(StringUtil::normalizeCsv((string)($option['permissions'] ?? '')))
            ->setOptions(StringUtil::normalizeCsv((string)($option['options'] ?? '')))
            ->setAdditionalData($additionalData)
            ->setOriginIsSystem(true);

        if (isset($option['required'])) {
            $builder->setRequired((bool)$option['required']);
        }
        if (isset($option['askduringregistration'])) {
            $builder->setAskDuringRegistration((bool)$option['askduringregistration']);
        }
        if (isset($option['searchable'])) {
            $builder->setSearchable((bool)$option['searchable']);
        }

        if ($builder->isUpdate()) {
            $builder->update();
        } else {
            $builder->create();
        }
    }

    /**
     * Returns the option with the given name if it is provided by this package.
     */
    private function getExistingOption(string $optionName): ?UserOption
    {
        $sql = "SELECT  *
                FROM    wcf1_user_option
                WHERE   optionName = ?
                    AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $optionName,
            $this->installation->getPackageID(),
        ]);

        return $statement->fetchObject(UserOption::class);
    }

    #[\Override]
    public function hasUninstall()
    {
        if (parent::hasUninstall()) {
            return true;
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user_option_category
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);

        return $statement->fetchSingleColumn() > 0;
    }

    #[\Override]
    public function uninstall()
    {
        $sql = "SELECT  optionID
                FROM    wcf1_user_option
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
        $optionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        if ($optionIDs !== []) {
            UserOptionBuilder::deleteAll($optionIDs);
        }

        parent::uninstall();

        $sql = "SELECT  categoryID
                FROM    wcf1_user_option_category
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
        $categoryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        if ($categoryIDs !== []) {
            UserOptionCategoryBuilder::deleteAll($categoryIDs);
        }

        UserOptionBuilder::resetCache();
    }

    #[\Override]
    protected function handleDelete(array $items) {}

    #[\Override]
    protected function prepareImport(array $data)
    {
        return $data;
    }

    #[\Override]
    protected function findExistingItem(array $data)
    {
        return null;
    }

    #[\Override]
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function getEntryTypes()
    {
        return ['options', 'categories'];
    }

    /**
     * @return void
     */
    #[\Override]
    protected function addFormFields(IFormDocument $form)
    {
        $dataContainer = $form->getNodeById('data');
        \assert($dataContainer instanceof IFormContainer);

        switch ($this->entryType) {
            case 'categories':
                $dataContainer->appendChildren([
                    TextFormField::create('categoryName')
                        ->objectProperty('name')
                        ->label('wcf.acp.pip.abstractOption.categories.categoryName')
                        ->required(),

                    SingleSelectionFormField::create('parentCategoryName')
                        ->objectProperty('parent')
                        ->label('wcf.acp.pip.abstractOption.categories.parentCategoryName')
                        ->options(function () {
                            $options = [
                                [
                                    'depth' => 0,
                                    'label' => WCF::getLanguage()->get('wcf.global.noSelection'),
                                    'value' => '',
                                ],
                            ];

                            // The maximum nesting level is three.
                            foreach ($this->getCategoryOptions() as $option) {
                                if ($option['depth'] <= 1) {
                                    $options[] = $option;
                                }
                            }

                            return $options;
                        }, true),

                    IntegerFormField::create('showOrder')
                        ->objectProperty('showorder')
                        ->label('wcf.form.field.showOrder')
                        ->description('wcf.acp.pip.abstractOption.categories.showOrder.description')
                        ->nullable(),

                    OptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.categories.options.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs($this->getRelevantPackageIDs()),

                    UserGroupOptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.categories.permissions.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs($this->getRelevantPackageIDs()),
                ]);

                break;

            case 'options':
                $dataContainer->appendChildren([
                    TextFormField::create('optionName')
                        ->objectProperty('name')
                        ->label('wcf.acp.pip.abstractOption.options.optionName')
                        ->description('wcf.acp.pip.abstractOption.options.optionName.description')
                        ->required(),

                    SingleSelectionFormField::create('categoryName')
                        ->objectProperty('categoryname')
                        ->label('wcf.acp.pip.abstractOption.options.categoryName')
                        ->required()
                        ->filterable()
                        ->options(function (): array {
                            $options = $this->getCategoryOptions();

                            // Options of the `hidden` category are not shown to users.
                            $options[] = [
                                'depth' => 0,
                                'label' => 'hidden',
                                'value' => 'hidden',
                            ];

                            return $options;
                        }, true, false),

                    SingleSelectionFormField::create('optionType')
                        ->objectProperty('optiontype')
                        ->label('wcf.acp.pip.abstractOption.options.optionType')
                        ->description('wcf.acp.pip.userOption.options.optionType.description')
                        ->required()
                        ->options($this->getOptionTypeOptions()),

                    MultilineTextFormField::create('defaultValue')
                        ->objectProperty('defaultvalue')
                        ->label('wcf.acp.pip.abstractOption.options.defaultValue')
                        ->description('wcf.acp.pip.abstractOption.options.defaultValue.description')
                        ->rows(5),

                    TextFormField::create('validationPattern')
                        ->objectProperty('validationpattern')
                        ->label('wcf.acp.pip.abstractOption.options.validationPattern')
                        ->description('wcf.acp.pip.abstractOption.options.validationPattern.description')
                        ->addValidator($this->getRegexValidator(
                            'wcf.acp.pip.abstractOption.options.validationPattern.error.invalid'
                        )),

                    MultilineTextFormField::create('selectOptions')
                        ->objectProperty('selectoptions')
                        ->label('wcf.acp.pip.abstractOption.options.selectOptions')
                        ->description('wcf.acp.pip.abstractOption.options.selectOptions.description')
                        ->rows(5),

                    MultilineTextFormField::create('enableOptions')
                        ->objectProperty('enableoptions')
                        ->label('wcf.acp.pip.abstractOption.options.enableOptions')
                        ->description('wcf.acp.pip.abstractOption.options.enableOptions.description')
                        ->rows(5),

                    IntegerFormField::create('showOrder')
                        ->objectProperty('showorder')
                        ->label('wcf.form.field.showOrder')
                        ->description('wcf.acp.pip.abstractOption.options.showOrder.description'),

                    OptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.options.options.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs($this->getRelevantPackageIDs()),

                    UserGroupOptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.options.permissions.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs($this->getRelevantPackageIDs()),

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
                            foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message') as $objectType) {
                                $options[$objectType->objectType] = $objectType->objectType;
                            }

                            \asort($options);

                            return $options;
                        }, false, false),

                    TextFormField::create('contentPattern')
                        ->objectProperty('contentpattern')
                        ->label('wcf.acp.pip.userOption.options.contentPattern')
                        ->description('wcf.acp.pip.userOption.options.contentPattern.description')
                        ->addValidator($this->getRegexValidator(
                            'wcf.acp.pip.userOption.options.contentPattern.error.invalid'
                        )),
                ]);

                $optionType = $form->getFormField('optionType');
                $form->getFormField('selectOptions')->addDependency(
                    ValueFormFieldDependency::create('optionType')
                        ->field($optionType)
                        ->values($this->getSelectOptionOptionTypes())
                );
                $form->getFormField('messageObjectType')->addDependency(
                    ValueFormFieldDependency::create('optionType')
                        ->field($optionType)
                        ->values(['wysiwyg'])
                );
                $form->getFormField('contentPattern')->addDependency(
                    ValueFormFieldDependency::create('optionType')
                        ->field($optionType)
                        ->values(['text'])
                );

                // ensure proper normalization of the multi-line values
                $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                    'multilineValues',
                    static function (IFormDocument $document, array $parameters) {
                        foreach (['defaultvalue', 'enableoptions', 'selectoptions'] as $key) {
                            if (isset($parameters['data'][$key])) {
                                $parameters['data'][$key] = StringUtil::unifyNewlines($parameters['data'][$key]);
                            }
                        }

                        return $parameters;
                    }
                ));

                break;

            default:
                throw new \LogicException('Unreachable');
        }
    }

    private function getRegexValidator(string $errorLanguageItem): FormFieldValidator
    {
        return new FormFieldValidator('regex', static function (TextFormField $formField) use ($errorLanguageItem) {
            if ($formField->getSaveValue() !== '' && !Regex::compile($formField->getSaveValue())->isValid()) {
                $formField->addValidationError(new FormFieldValidationError('invalid', $errorLanguageItem));
            }
        });
    }

    /**
     * Returns the selectable option types, the form option types and the
     * option types that are mapped to a form option.
     *
     * @return array<string, string>
     */
    protected function getOptionTypeOptions(): array
    {
        $options = FormOptionHandler::getInstance()->getSortedOptionTypes();

        // The birthday is stored as a date, but has a special meaning, e.g. for the age.
        $options['birthday'] = WCF::getLanguage()->get('wcf.user.option.birthday');

        return $options;
    }

    /**
     * Returns the ids of the form option types that support select options.
     *
     * @return list<string>
     */
    protected function getSelectOptionOptionTypes(): array
    {
        $optionTypes = [];
        foreach (FormOptionHandler::getInstance()->getOptions() as $id => $formOption) {
            if (\in_array('selectOptions', $formOption->getConfigurationFormFields(), true)) {
                $optionTypes[] = $id;
            }
        }

        return $optionTypes;
    }

    /**
     * Returns the ids of this package and the packages it requires.
     *
     * @return list<int>
     */
    private function getRelevantPackageIDs(): array
    {
        return \array_merge(
            [$this->installation->getPackage()->packageID],
            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
        );
    }

    /**
     * Returns the categories of this package and the packages it requires as
     * nested options for a selection form field.
     *
     * @return list<array{depth: int, label: string, value: string}>
     */
    private function getCategoryOptions(): array
    {
        $options = [];
        foreach ($this->getSortedCategories() as $categoryName => ['category' => $category, 'depth' => $depth]) {
            $options[] = [
                'depth' => $depth,
                'label' => $categoryName,
                'value' => $categoryName,
            ];
        }

        return $options;
    }

    /**
     * Returns the categories of this package and the packages it requires in
     * the order of the category tree, with array keys being the category names.
     *
     * @return array<string, array{category: OptionCategory, depth: int}>
     */
    public function getSortedCategories(): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');
        $relevantPackageIDs = $this->getRelevantPackageIDs();

        $sortedCategories = [];
        $addCategories = static function (string $parentCategoryName, int $depth) use (
            $categories,
            $categoryStructure,
            $relevantPackageIDs,
            &$sortedCategories,
            &$addCategories
        ): void {
            foreach ($categoryStructure[$parentCategoryName] ?? [] as $categoryName) {
                $category = $categories[$categoryName];
                if (!\in_array($category->packageID, $relevantPackageIDs, true)) {
                    continue;
                }

                $sortedCategories[$categoryName] = [
                    'category' => $category,
                    'depth' => $depth,
                ];
                $addCategories($categoryName, $depth + 1);
            }
        };
        $addCategories('', 0);

        return $sortedCategories;
    }

    /**
     * @return array<string, int|string|null>
     */
    #[\Override]
    protected function fetchElementData(\DOMElement $element, bool $saveData)
    {
        return match ($this->entryType) {
            'categories' => $this->fetchCategoryData($element, $saveData),
            'options' => $this->fetchOptionData($element, $saveData),
            default => throw new \LogicException('Unreachable'),
        };
    }

    /**
     * @return array<string, int|string|null>
     */
    private function fetchCategoryData(\DOMElement $element, bool $saveData): array
    {
        $data = [
            'categoryName' => $element->getAttribute('name'),
        ];

        $parent = $element->getElementsByTagName('parent')->item(0);
        if ($parent !== null) {
            $data['parentCategoryName'] = $parent->nodeValue;
        } elseif ($saveData) {
            $data['parentCategoryName'] = '';
        }

        foreach (['options', 'permissions'] as $propertyName) {
            $property = $element->getElementsByTagName($propertyName)->item(0);
            if ($property !== null) {
                $data[$propertyName] = StringUtil::normalizeCsv($property->nodeValue);
            } elseif ($saveData) {
                $data[$propertyName] = '';
            }
        }

        $showOrder = $element->getElementsByTagName('showorder')->item(0);
        if ($showOrder !== null) {
            $data['showOrder'] = (int)$showOrder->nodeValue;
        }
        if ($saveData && $this->editedEntry === null) {
            // only set an explicit show order when adding new categories
            $data['showOrder'] = $this->getShowOrder(
                $data['showOrder'] ?? null,
                $data['parentCategoryName'],
                'parentCategoryName',
                '_category'
            );
        }

        return $data;
    }

    /**
     * Returns the data of the option, the keys are the names of the XML elements
     * when saving the data and the ids of the form fields otherwise.
     *
     * @return array<string, int|string|null>
     */
    private function fetchOptionData(\DOMElement $element, bool $saveData): array
    {
        $properties = [
            'categoryname' => 'categoryName',
            'optiontype' => 'optionType',
            'defaultvalue' => 'defaultValue',
            'validationpattern' => 'validationPattern',
            'selectoptions' => 'selectOptions',
            'enableoptions' => 'enableOptions',
            'showorder' => 'showOrder',
            'options' => 'options',
            'permissions' => 'permissions',
            'required' => 'required',
            'askduringregistration' => 'askDuringRegistration',
            'editable' => 'editable',
            'visible' => 'visible',
            'searchable' => 'searchable',
            'isdisabled' => 'isDisabled',
            'messageObjectType' => 'messageObjectType',
            'contentpattern' => 'contentPattern',
        ];

        $data = [
            $saveData ? 'name' : 'optionName' => $element->getAttribute('name'),
        ];
        foreach ($properties as $tagName => $fieldID) {
            $property = $element->getElementsByTagName($tagName)->item(0);
            if ($property === null) {
                continue;
            }

            $value = $property->nodeValue;
            if ($tagName === 'options' || $tagName === 'permissions') {
                $value = StringUtil::normalizeCsv($value);
            }

            $data[$saveData ? $tagName : $fieldID] = $value;
        }

        if ($saveData && $this->editedEntry === null) {
            // only set an explicit show order when adding new options
            $data['showorder'] = $this->getShowOrder(
                isset($data['showorder']) ? (int)$data['showorder'] : null,
                (string)$data['categoryname'],
                'categoryName'
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('name');
    }

    /**
     * @return void
     */
    #[\Override]
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        match ($this->entryType) {
            'categories' => $entryList->setKeys([
                'categoryName' => 'wcf.acp.pip.abstractOption.categories.categoryName',
                'parentCategoryName' => 'wcf.acp.pip.abstractOption.categories.parentCategoryName',
            ]),
            'options' => $entryList->setKeys([
                'optionName' => 'wcf.acp.pip.abstractOption.options.optionName',
                'categoryName' => 'wcf.acp.pip.abstractOption.options.categoryName',
                'optionType' => 'wcf.acp.pip.abstractOption.options.optionType',
            ]),
            default => throw new \LogicException('Unreachable'),
        };
    }

    /**
     * @return \DOMElement
     */
    #[\Override]
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $formData = $form->getData()['data'];

        switch ($this->entryType) {
            case 'categories':
                $category = $document->createElement('category');
                $category->setAttribute('name', $formData['name']);

                foreach (['parent', 'showorder', 'options', 'permissions'] as $field) {
                    if (isset($formData[$field]) && $formData[$field] !== '') {
                        $category->appendChild($document->createElement($field, (string)$formData[$field]));
                    }
                }

                return $category;

            case 'options':
                $option = $document->createElement($this->tagName);
                $option->setAttribute('name', $formData['name']);

                $this->appendElementChildren(
                    $option,
                    [
                        'categoryname',
                        'optiontype',
                        'defaultvalue' => '',
                        'validationpattern' => '',
                        'selectoptions' => '',
                        'enableoptions' => '',
                        'showorder' => 0,
                        'options' => '',
                        'permissions' => '',
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

                return $option;

            default:
                throw new \LogicException('Unreachable');
        }
    }

    /**
     * @return void
     */
    protected function insertNewXmlElement(XML $xml, \DOMElement $newElement)
    {
        $import = $xml->xpath()->query('/ns:data/ns:import')->item(0);
        if ($import === null) {
            $data = $xml->xpath()->query('/ns:data')->item(0);
            \assert($data instanceof \DOMElement);
            $import = $xml->getDocument()->createElement('import');
            DOMUtil::prepend($import, $data);
        }

        $options = $xml->xpath()->query('/ns:data/ns:import/ns:options')->item(0);

        switch ($this->entryType) {
            case 'categories':
                $categories = $xml->xpath()->query('/ns:data/ns:import/ns:categories')->item(0);
                if ($categories === null) {
                    $categories = $xml->getDocument()->createElement('categories');

                    if ($options === null) {
                        $import->appendChild($categories);
                    } else {
                        DOMUtil::insertBefore($categories, $options);
                    }
                }

                $categories->appendChild($newElement);

                break;

            case 'options':
                if ($options === null) {
                    $options = $xml->getDocument()->createElement('options');
                    $import->appendChild($options);
                }

                $options->appendChild($newElement);

                break;
        }
    }

    /**
     * @return void
     */
    protected function saveObject(\DOMElement $newElement, ?\DOMElement $oldElement = null)
    {
        $data = $this->getElementData($newElement, true);

        match ($this->entryType) {
            'categories' => $this->saveCategory($data),
            'options' => $this->saveOption($data, (string)($data['categoryname'] ?? '')),
            default => throw new \LogicException('Unreachable'),
        };

        UserOptionBuilder::resetCache();
    }

    /**
     * @return ?\DOMElement
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $deleteElement = $element->ownerDocument->createElement(
            $this->entryType === 'categories' ? 'optioncategory' : 'option'
        );
        $deleteElement->setAttribute('name', $element->getAttribute('name'));

        return $deleteElement;
    }

    /**
     * @return void
     */
    protected function deleteObject(\DOMElement $element)
    {
        $name = $element->getAttribute('name');

        match ($this->entryType) {
            'categories' => $this->deleteCategories([$name]),
            'options' => $this->deleteOptions('optionName', [$name]),
            default => throw new \LogicException('Unreachable'),
        };
    }

    /**
     * @return void
     */
    protected function addDeleteElement(\DOMElement $element)
    {
        $this->defaultAddDeleteElement($element);

        // The options of deleted categories are deleted with the category,
        // explicit delete instructions are not necessary.
        if ($this->entryType !== 'categories') {
            return;
        }

        $xpath = new \DOMXPath($element->ownerDocument);
        $xpath->registerNamespace('ns', $element->ownerDocument->documentElement->getAttribute('xmlns'));

        $options = $xpath->query('/ns:data/ns:import/ns:options')->item(0);
        if (!($options instanceof \DOMElement)) {
            return;
        }

        $categoryName = $element->getAttribute('name');
        foreach (DOMUtil::getElements($options, 'option') as $option) {
            if ($option->getElementsByTagName('categoryname')->item(0)?->nodeValue === $categoryName) {
                DOMUtil::removeNode($option);
            }
        }
    }

    /**
     * @return bool
     */
    protected function sanitizeXmlFileAfterDeleteEntry(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('ns', $document->documentElement->getAttribute('xmlns'));

        foreach (['categories', 'options'] as $type) {
            $element = $xpath->query('/ns:data/ns:import/ns:' . $type)->item(0);
            if ($element !== null && $element->childNodes->length === 0) {
                DOMUtil::removeNode($element);
            }
        }

        return $this->defaultSanitizeXmlFileAfterDeleteEntry($document);
    }
}
