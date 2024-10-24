<?php

namespace wcf\system\package\plugin;

use wcf\data\DatabaseObject;
use wcf\data\IEditableCachedObject;
use wcf\data\option\category\OptionCategory;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
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
use wcf\system\option\II18nOptionType;
use wcf\system\option\IntegerOptionType;
use wcf\system\option\IOptionHandler;
use wcf\system\option\IOptionType;
use wcf\system\option\ISelectOptionOptionType;
use wcf\system\option\TextOptionType;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Abstract implementation of a package installation plugin for options.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractOptionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IIdempotentPackageInstallationPlugin
{
    // we do no implement `IGuiPackageInstallationPlugin` but instead just
    // provide the default implementation to ensure backwards compatibility
    // with third-party packages containing classes that extend this abstract
    // class
    use TXmlGuiPackageInstallationPlugin {
        addDeleteElement as defaultAddDeleteElement;

        sanitizeXmlFileAfterDeleteEntry as defaultSanitizeXmlFileAfterDeleteEntry;
    }

    /**
     * list of option types with i18n support
     * @var string[]
     */
    public $i18nOptionTypes = [];

    /**
     * list of integer-like option types
     * @var string[]
     */
    public $integerOptionTypes = [];

    /**
     * list of option types with i18n support extending `TextOptionType`
     * (in addition to `text`)
     * @var string[]
     */
    public $textOptionTypes = ['text'];

    /**
     * list of option types with a pre-defined list of options via `selectOptions`
     * @var string[]
     */
    public $selectOptionOptionTypes = [];

    /**
     * @inheritDoc
     */
    public function install()
    {
        AbstractPackageInstallationPlugin::install();

        $xml = $this->getXML($this->instruction['value']);
        $xpath = $xml->xpath();

        if ($this->installation->getAction() == 'update') {
            // handle delete first
            $this->deleteItems($xpath);
        }

        // import or update categories
        $this->importCategories($xpath);

        // import or update options
        $this->importOptions($xpath);
    }

    /**
     * @inheritDoc
     */
    protected function deleteItems(\DOMXPath $xpath)
    {
        // delete options
        $elements = $xpath->query('/ns:data/ns:delete/ns:option');
        $options = [];

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $options[] = $element->getAttribute('name');
        }

        if (!empty($options)) {
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                    WHERE       optionName = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($options as $option) {
                $statement->execute([
                    $option,
                    $this->installation->getPackageID(),
                ]);
            }
        }

        // delete categories
        $elements = $xpath->query('/ns:data/ns:delete/ns:optioncategory');
        $categories = [];

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $categories[] = $element->getAttribute('name');
        }

        if (!empty($categories)) {
            // delete options for given categories
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                    WHERE       categoryName = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($categories as $category) {
                $statement->execute([
                    $category,
                    $this->installation->getPackageID(),
                ]);
            }

            // delete categories
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "_category
                    WHERE       categoryName = ?
                    AND         packageID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($categories as $category) {
                $statement->execute([
                    $category,
                    $this->installation->getPackageID(),
                ]);
            }
        }
    }

    /**
     * Imports option categories.
     *
     * @param \DOMXPath $xpath
     * @throws  SystemException
     */
    protected function importCategories(\DOMXPath $xpath)
    {
        $elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $data = [];

            // get child elements
            $children = $xpath->query('child::*', $element);
            foreach ($children as $child) {
                $data[$child->tagName] = $child->nodeValue;
            }

            // build data block with defaults
            $data = [
                'categoryName' => $element->getAttribute('name'),
                'options' => isset($data['options']) ? StringUtil::normalizeCsv($data['options']) : '',
                'parentCategoryName' => $data['parent'] ?? '',
                'permissions' => isset($data['permissions']) ? StringUtil::normalizeCsv($data['permissions']) : '',
                'showOrder' => isset($data['showorder']) ? \intval($data['showorder']) : null,
            ];

            // adjust show order
            if ($data['showOrder'] !== null || $this->installation->getAction() != 'update' || $this->getExistingCategory($element->getAttribute('name')) === false) {
                $data['showOrder'] = $this->getShowOrder(
                    $data['showOrder'],
                    $data['parentCategoryName'],
                    'parentCategoryName',
                    '_category'
                );
            }

            // validate parent
            if (!empty($data['parentCategoryName'])) {
                $sql = "SELECT  COUNT(categoryID)
                        FROM    " . $this->application . "1_" . $this->tableName . "_category
                        WHERE   categoryName = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$data['parentCategoryName']]);

                if (!$statement->fetchSingleColumn()) {
                    throw new SystemException("Unable to find parent 'option category' with name '" . $data['parentCategoryName'] . "' for category with name '" . $data['categoryName'] . "'.");
                }
            }

            // save category
            $this->saveCategory($data);
        }
    }

    /**
     * Imports options.
     *
     * @param \DOMXPath $xpath
     */
    protected function importOptions(\DOMXPath $xpath)
    {
        $elements = $xpath->query('/ns:data/ns:import/ns:options/ns:option');

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $data = [];
            $children = $xpath->query('child::*', $element);
            foreach ($children as $child) {
                $data[$child->tagName] = $child->nodeValue;
            }

            $data['name'] = $element->getAttribute('name');

            $this->validateOption($data);
            $this->saveOption($data, $data['categoryname']);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasUninstall()
    {
        $hasUninstallOptions = parent::hasUninstall();
        $sql = "SELECT  COUNT(categoryID)
                FROM    " . $this->application . "1_" . $this->tableName . "_category
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);

        return $hasUninstallOptions || $statement->fetchSingleColumn() > 0;
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // delete options
        parent::uninstall();

        // delete categories
        $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "_category
                WHERE       packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
    }

    /**
     * Returns the category with given name.
     *
     * @param string $category
     * @return      array|false
     */
    protected function getExistingCategory($category)
    {
        $sql = "SELECT  categoryID, packageID
                FROM    " . $this->application . "1_" . $this->tableName . "_category
                WHERE   categoryName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $category,
        ]);

        return $statement->fetchArray();
    }

    /**
     * Installs option categories.
     *
     * @param array $category
     * @throws  SystemException
     */
    protected function saveCategory($category)
    {
        // search existing category
        $row = $this->getExistingCategory($category['categoryName']);
        if (empty($row['categoryID'])) {
            // insert new category
            $sql = "INSERT INTO " . $this->application . "1_" . $this->tableName . "_category
                                (packageID, categoryName, parentCategoryName, permissions,
                                options" . ($category['showOrder'] !== null ? ",showOrder" : "") . ")
                    VALUES      (?, ?, ?, ?, ?" . ($category['showOrder'] !== null ? ", ?" : "") . ")";
            $statement = WCF::getDB()->prepare($sql);

            $data = [
                $this->installation->getPackageID(),
                $category['categoryName'],
                $category['parentCategoryName'],
                $category['permissions'],
                $category['options'],
            ];
            if ($category['showOrder'] !== null) {
                $data[] = $category['showOrder'];
            }

            $statement->execute($data);
        } else {
            if ($row['packageID'] != $this->installation->getPackageID()) {
                throw new SystemException("Cannot override existing category '" . $category['categoryName'] . "'");
            }

            // update existing category
            $sql = "UPDATE  " . $this->application . "1_" . $this->tableName . "_category
                    SET     parentCategoryName = ?,
                            permissions = ?,
                            options = ?
                            " . (isset($category['showOrder']) ? ", showOrder = ?" : "") . "
                    WHERE   categoryID = ?";
            $statement = WCF::getDB()->prepare($sql);

            $data = [
                $category['parentCategoryName'] ?? '',
                $category['permissions'] ?? '',
                $category['options'] ?? '',
            ];
            if (isset($category['showOrder'])) {
                $data[] = $category['showOrder'];
            }
            $data[] = $row['categoryID'];

            $statement->execute($data);
        }
    }

    /**
     * Installs options.
     *
     * @param array $option
     * @param string $categoryName
     * @param int $existingOptionID
     */
    abstract protected function saveOption($option, $categoryName, $existingOptionID = 0);

    /**
     * @inheritDoc
     */
    protected function validateOption(array $data)
    {
        if (!\preg_match("/^[\\w\\-\\.]+$/", $data['name'])) {
            $matches = [];
            \preg_match_all("/(\\W)/", $data['name'], $matches);
            throw new SystemException("The option '" . $data['name'] . "' has at least one non-alphanumeric character (underscore is permitted): (" . \implode(
                "), ( ",
                $matches[1]
            ) . ").");
        }

        // check if option already exists
        $sql = "SELECT  *
                FROM    " . $this->application . "1_" . $this->tableName . "
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $data['name'],
        ]);
        $row = $statement->fetchArray();
        if ($row && $row['packageID'] != $this->installation->getPackageID()) {
            $package = new Package($row['packageID']);
            throw new SystemException($this->tableName . " '" . $data['name'] . "' is already provided by '" . $package . "' ('" . $package->package . "').");
        }
    }

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var IFormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

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
                            $categories = $this->getSortedCategories();

                            $getDepth = static function (/** @var OptionCategory $category */ $category) use (
                                $categories
                            ) {
                                $depth = 0;

                                while (isset($categories[$category->parentCategoryName])) {
                                    $depth++;

                                    $category = $categories[$category->parentCategoryName];
                                }

                                return $depth;
                            };

                            $options = [
                                [
                                    'depth' => 0,
                                    'label' => WCF::getLanguage()->get('wcf.global.noSelection'),
                                    'value' => '',
                                ],
                            ];
                            /** @var OptionCategory $category */
                            foreach ($categories as $category) {
                                $depth = $getDepth($category);

                                // the maximum nesting level is three
                                if ($depth <= 1) {
                                    $options[] = [
                                        'depth' => $depth,
                                        'label' => $category->categoryName,
                                        'value' => $category->categoryName,
                                    ];
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
                        ->packageIDs(\array_merge(
                            [$this->installation->getPackage()->packageID],
                            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                        )),

                    UserGroupOptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.categories.permissions.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs(\array_merge(
                            [$this->installation->getPackage()->packageID],
                            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                        )),
                ]);

                break;

            case 'options':
                $classnamePieces = \explode('\\', static::class);
                $pipPrefix = \str_replace('PackageInstallationPlugin', '', \array_pop($classnamePieces));

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
                            $categories = $this->getSortedCategories();

                            $getDepth = static function (/** @var OptionCategory $category */ $category) use (
                                $categories
                            ) {
                                $depth = 0;

                                while (isset($categories[$category->parentCategoryName])) {
                                    $depth++;

                                    $category = $categories[$category->parentCategoryName];
                                }

                                return $depth;
                            };

                            $options = [];

                            // https://github.com/squizlabs/PHP_CodeSniffer/issues/3199
                            // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
                            foreach ($categories as $category) {
                                /** @var OptionCategory $category */
                                $options[] = [
                                    'depth' => $getDepth($category),
                                    'label' => $category->categoryName,
                                    'value' => $category->categoryName,
                                ];
                            }

                            return $options;
                        }, true, false),

                    SingleSelectionFormField::create('optionType')
                        ->objectProperty('optiontype')
                        ->label('wcf.acp.pip.abstractOption.options.optionType')
                        ->description('wcf.acp.pip.' . \lcfirst($pipPrefix) . '.options.optionType.description')
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
                        ->addValidator(new FormFieldValidator('regex', static function (TextFormField $formField) {
                            if ($formField->getSaveValue() !== '') {
                                if (!Regex::compile($formField->getSaveValue())->isValid()) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.pip.abstractOption.options.validationPattern.error.invalid'
                                        )
                                    );
                                }
                            }
                        })),

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
                        ->packageIDs(\array_merge(
                            [$this->installation->getPackage()->packageID],
                            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                        )),

                    UserGroupOptionFormField::create()
                        ->description('wcf.acp.pip.abstractOption.options.permissions.description')
                        ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                        ->packageIDs(\array_merge(
                            [$this->installation->getPackage()->packageID],
                            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                        )),
                ]);

                /** @var SingleSelectionFormField $optionType */
                $optionType = $form->getNodeById('optionType');

                // add option-specific fields
                $dataContainer->appendChildren([
                    IntegerFormField::create('minValue')
                        ->objectProperty('minvalue')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.integer.minValue')
                        ->nullable()
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(\array_merge($this->integerOptionTypes, ['fileSize']))
                        ),

                    IntegerFormField::create('maxValue')
                        ->objectProperty('maxvalue')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.integer.maxValue')
                        ->nullable()
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(\array_merge($this->integerOptionTypes, ['fileSize']))
                        ),

                    TextFormField::create('suffix')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.integer.suffix')
                        ->description('wcf.acp.pip.abstractOption.options.optionType.integer.suffix.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values($this->integerOptionTypes)
                        ),

                    IntegerFormField::create('minLength')
                        ->objectProperty('minlength')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.text.minLength')
                        ->minimum(0)
                        ->nullable()
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(['password', 'text', 'textarea', 'URL'])
                        ),

                    IntegerFormField::create('maxLength')
                        ->objectProperty('maxlength')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.text.maxLength')
                        ->minimum(1)
                        ->nullable()
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(['password', 'text', 'textarea', 'URL'])
                        ),

                    BooleanFormField::create('isSortable')
                        ->objectProperty('issortable')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.useroptions.isSortable')
                        ->description('wcf.acp.pip.abstractOption.options.optionType.useroptions.isSortable.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(['useroptions'])
                        ),

                    BooleanFormField::create('allowEmptyValue')
                        ->objectProperty('allowemptyvalue')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue')
                        ->description('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(['captchaSelect'])
                        ),

                    BooleanFormField::create('allowEmptyValue_select')
                        ->objectProperty('allowEmptyValue')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue')
                        ->description('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values(['select'])
                        ),

                    BooleanFormField::create('disableAutocomplete')
                        ->label('wcf.acp.pip.abstractOption.options.optionType.text.disableAutocomplete')
                        ->description('wcf.acp.pip.abstractOption.options.optionType.text.disableAutocomplete.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->field($optionType)
                                ->values($this->textOptionTypes)
                        ),
                ]);

                // ensure proper normalization of default value and enable options
                $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                    'enableOptions',
                    static function (IFormDocument $document, array $parameters) {
                        if (isset($parameters['data']['enableoptions'])) {
                            $parameters['data']['enableoptions'] = StringUtil::unifyNewlines($parameters['data']['enableoptions']);
                        }

                        if (isset($parameters['data']['defaultvalue'])) {
                            $parameters['data']['defaultvalue'] = StringUtil::unifyNewlines($parameters['data']['defaultvalue']);
                        }

                        return $parameters;
                    }
                ));

                break;

            default:
                throw new \LogicException('Unreachable');
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [];

        switch ($this->entryType) {
            case 'categories':
                $data['categoryName'] = $element->getAttribute('name');

                $parent = $element->getElementsByTagName('parent')->item(0);
                if ($parent !== null) {
                    $data['parentCategoryName'] = $parent->nodeValue;
                } elseif ($saveData) {
                    $data['parentCategoryName'] = '';
                }

                foreach (['options', 'permissions'] as $optionalPropertyName) {
                    $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                    if ($optionalProperty !== null) {
                        $data[$optionalPropertyName] = StringUtil::normalizeCsv($optionalProperty->nodeValue);
                    } elseif ($saveData) {
                        $data[$optionalPropertyName] = '';
                    }
                }

                $showOrder = $element->getElementsByTagName('showorder')->item(0);
                if ($showOrder !== null) {
                    $data['showOrder'] = $showOrder->nodeValue;
                }
                if ($saveData && $this->editedEntry === null) {
                    // only set explicit showOrder when adding new categories
                    $data['showOrder'] = $this->getShowOrder(
                        $data['showOrder'] ?? null,
                        $data['parentCategoryName'],
                        'parentCategoryName',
                        '_category'
                    );
                }

                break;

            case 'options':
                if (!$saveData) {
                    $data['optionName'] = $element->getAttribute('name');
                    $data['categoryName'] = $element->getElementsByTagName('categoryname')->item(0)->nodeValue;
                    $data['optionType'] = $element->getElementsByTagName('optiontype')->item(0)->nodeValue;

                    foreach (['defaultValue', 'enableOptions', 'validationPattern'] as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName(\strtolower($optionalPropertyName))->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        }
                    }

                    $showOrder = $element->getElementsByTagName('showorder')->item(0);
                    if ($showOrder !== null) {
                        $data['showOrder'] = $showOrder->nodeValue;
                    }

                    foreach (['options', 'permissions'] as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = StringUtil::normalizeCsv($optionalProperty->nodeValue);
                        }
                    }

                    // object-type specific elements
                    $optionals = [
                        'minvalue',
                        'maxvalue',
                        'suffix',
                        'minlength',
                        'maxlength',
                        'issortable',
                        'allowemptyvalue',
                        'disableAutocomplete',
                    ];

                    foreach ($optionals as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        }
                    }
                } else {
                    $data['name'] = $element->getAttribute('name');
                    $data['categoryname'] = $element->getElementsByTagName('categoryname')->item(0)->nodeValue;
                    $data['optiontype'] = $element->getElementsByTagName('optiontype')->item(0)->nodeValue;

                    foreach (['defaultvalue', 'enableoptions', 'validationpattern'] as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        } elseif ($optionalPropertyName === 'defaultvalue') {
                            $data[$optionalPropertyName] = null;
                        } else {
                            $data[$optionalPropertyName] = '';
                        }
                    }

                    $showOrder = $element->getElementsByTagName('showorder')->item(0);
                    if ($showOrder !== null) {
                        $data['showorder'] = $showOrder->nodeValue;
                    }
                    if ($this->editedEntry === null) {
                        // only set explicit showOrder when adding new categories
                        $data['showorder'] = $this->getShowOrder(
                            $data['showorder'] ?? null,
                            $data['categoryname'],
                            'categoryname'
                        );
                    }

                    foreach (['options', 'permissions'] as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = StringUtil::normalizeCsv($optionalProperty->nodeValue);
                        } else {
                            $daota[$optionalPropertyName] = '';
                        }
                    }

                    // object-type specific elements
                    $optionals = [
                        'minvalue',
                        'maxvalue',
                        'suffix',
                        'minlength',
                        'maxlength',
                        'issortable',
                        'allowemptyvalue',
                        'disableAutocomplete',
                    ];

                    foreach ($optionals as $optionalPropertyName) {
                        $optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
                        if ($optionalProperty !== null) {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        }
                    }
                }

                break;

            default:
                throw new \LogicException('Unreachable');
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('name');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getEntryTypes()
    {
        return ['options', 'categories'];
    }

    /**
     * Returns a list of sorted categories with array keys being the category names.
     *
     * @return  DatabaseObject[]
     */
    public function getSortedCategories()
    {
        $optionHandler = $this->getSortOptionHandler();
        if ($optionHandler === null) {
            throw new \LogicException("Missing option handler");
        }
        $optionHandler->init();

        // only consider categories of relevant packages
        $relevantPackageIDs = \array_merge(
            [$this->installation->getPackage()->packageID],
            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
        );

        $buildSortedCategories = static function ($parentCategories) use (
            $relevantPackageIDs,
            &$buildSortedCategories
        ) {
            $categories = [];
            foreach ($parentCategories as $categoryData) {
                /** @var OptionCategory $category */
                $category = $categoryData['object'];

                if (\in_array($category->packageID, $relevantPackageIDs)) {
                    $categories[$category->categoryName] = $category;

                    $categories = \array_merge($categories, $buildSortedCategories($categoryData['categories']));
                }
            }

            return $categories;
        };

        return $buildSortedCategories($optionHandler->getOptionTree());
    }

    /**
     * Returns an option handler used for sorting.
     *
     * @return  IOptionHandler
     * @see     OptionPackageInstallationPlugin::getSortOptionHandler()
     */
    protected function getSortOptionHandler()
    {
        return null;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function saveObject(\DOMElement $newElement, ?\DOMElement $oldElement = null)
    {
        switch ($this->entryType) {
            case 'categories':
                $this->saveCategory($this->getElementData($newElement, true));

                break;

            case 'options':
                $optionData = $this->getElementData($newElement, true);

                $this->saveOption($optionData, $optionData['categoryname'] ?? '');

                break;
        }

        $this->postImport();

        if (\is_subclass_of($this->className, IEditableCachedObject::class)) {
            \call_user_func([$this->className, 'resetCache']);
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        switch ($this->entryType) {
            case 'categories':
                $entryList->setKeys([
                    'categoryName' => 'wcf.acp.pip.abstractOption.categories.categoryName',
                    'parentCategoryName' => 'wcf.acp.pip.abstractOption.categories.parentCategoryName',
                ]);
                break;

            case 'options':
                $entryList->setKeys([
                    'optionName' => 'wcf.acp.pip.abstractOption.options.optionName',
                    'categoryName' => 'wcf.acp.pip.abstractOption.options.categoryName',
                    'optionType' => 'wcf.acp.pip.abstractOption.options.optionType',
                ]);
                break;

            default:
                throw new \LogicException('Unreachable');
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $formData = $form->getData()['data'];

        switch ($this->entryType) {
            case 'categories':
                $category = $document->createElement('category');
                $category->setAttribute('name', $formData['name']);

                $document->getElementsByTagName('import')->item(0)->appendChild($category);

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
                        'enableoptions' => '',
                        'showorder' => 0,
                        'options' => '',
                        'permissions' => '',

                        // option type-specific elements
                        'minvalue' => null,
                        'maxvalue' => null,
                        'suffix' => '',
                        'minlength' => null,
                        'maxlength' => null,
                        'issortable' => 0,
                        'allowemptyvalue' => 0,
                        'disableAutocomplete' => 0,
                    ],
                    $form
                );

                return $option;

            default:
                throw new \LogicException('Unreachable');
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function insertNewXmlElement(XML $xml, \DOMElement $newElement)
    {
        $import = $xml->xpath()->query('/ns:data/ns:import')->item(0);
        if ($import === null) {
            $data = $xml->xpath()->query('/ns:data')->item(0);
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
                        $xml->xpath()->query('/ns:data/ns:import')->item(0)->appendChild($categories);
                    } else {
                        DOMUtil::insertBefore($categories, $options);
                    }
                }

                $categories->appendChild($newElement);

                break;

            case 'options':
                if ($options === null) {
                    $options = $xml->getDocument()->createElement('options');
                    $xml->xpath()->query('/ns:data/ns:import')->item(0)->appendChild($options);
                }

                $options->appendChild($newElement);

                break;
        }
    }

    /**
     * Returns the options for the option type form field.
     *
     * @return  array
     * @since   5.2
     */
    protected function getOptionTypeOptions()
    {
        $options = [];

        // consider all applications for potential object types
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            $optionDir = $application->getPackage()->getAbsolutePackageDir() . 'lib/system/option';

            if (!\is_dir($optionDir)) {
                continue;
            }

            $directoryUtil = DirectoryUtil::getInstance($optionDir);

            foreach ($directoryUtil->getFileObjects() as $fileObject) {
                if ($fileObject->isFile()) {
                    $namespace = $application->getAbbreviation() . '\\system\\option';
                    $unqualifiedClassname = \str_replace('.class.php', '', $fileObject->getFilename());
                    $classname = $namespace . '\\' . $unqualifiedClassname;

                    if (
                        !\is_subclass_of(
                            $classname,
                            IOptionType::class
                        ) || !(new \ReflectionClass($classname))->isInstantiable()
                    ) {
                        continue;
                    }

                    $optionType = \str_replace('OptionType.class.php', '', $fileObject->getFilename());

                    // only make first letter lowercase if the first two letters are not uppercase
                    // relevant cases: `URL` and the `WBB` prefix
                    if (!\preg_match('~^[A-Z]{2}~', $optionType)) {
                        $optionType = \lcfirst($optionType);
                    }

                    if (\is_subclass_of($classname, II18nOptionType::class)) {
                        $this->i18nOptionTypes[] = $optionType;
                    }

                    if (\is_subclass_of($classname, ISelectOptionOptionType::class)) {
                        $this->selectOptionOptionTypes[] = $optionType;
                    }

                    if (
                        $classname === IntegerOptionType::class || \is_subclass_of(
                            $classname,
                            IntegerOptionType::class
                        )
                    ) {
                        $this->integerOptionTypes[] = $optionType;
                    }

                    if ($classname === TextOptionType::class || \is_subclass_of($classname, TextOptionType::class)) {
                        $this->textOptionTypes[] = $optionType;
                    }

                    $options[] = $optionType;
                }
            }
        }

        \natcasesort($options);

        return \array_combine($options, $options);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $elementName = 'option';

        if ($this->entryType === 'categories') {
            $elementName .= 'category';
        }

        $deleteElement = $element->ownerDocument->createElement($elementName);
        $deleteElement->setAttribute('name', $element->getAttribute('name'));

        return $deleteElement;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $name = $element->getAttribute('name');

        switch ($this->entryType) {
            case 'categories':
                // also delete options
                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                        WHERE       categoryName = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->installation->getPackageID(),
                ]);

                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "_category
                        WHERE       categoryName = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->installation->getPackageID(),
                ]);

                break;

            case 'options':
                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                        WHERE       optionName = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->installation->getPackageID(),
                ]);

                break;
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addDeleteElement(\DOMElement $element)
    {
        $this->defaultAddDeleteElement($element);

        // remove install instructions for options in delete categories;
        // explicitly adding delete instructions for these options is not
        // necessary as they will be deleted automatically
        if ($this->entryType === 'categories') {
            $categoryName = $element->getAttribute('name');
            $objectType = $element->getElementsByTagName('objecttype')->item(0)->nodeValue;

            $xpath = new \DOMXPath($element->ownerDocument);
            $xpath->registerNamespace('ns', $element->ownerDocument->documentElement->getAttribute('xmlns'));

            $options = $xpath->query('/ns:data/ns:import/ns:options')->item(0);

            /** @var \DOMElement $option */
            foreach (DOMUtil::getElements($options, 'option') as $option) {
                $optionCategoryName = $option->getElementsByTagName('categoryname')->item(0);

                if ($optionCategoryName !== null) {
                    $optionObjectType = $option->getElementsByTagName('objectType')->item(0);
                    if ($optionCategoryName->nodeValue === $categoryName && $optionObjectType->nodeValue === $objectType) {
                        DOMUtil::removeNode($option);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function sanitizeXmlFileAfterDeleteEntry(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('ns', $document->documentElement->getAttribute('xmlns'));

        // remove empty categories and options elements
        foreach (['options'] as $type) {
            $element = $xpath->query('/ns:data/ns:import/ns:' . $type)->item(0);

            // remove empty options node
            if ($element !== null) {
                if ($element->childNodes->length === 0) {
                    DOMUtil::removeNode($element);
                }
            }
        }

        return $this->defaultSanitizeXmlFileAfterDeleteEntry($document);
    }
}
