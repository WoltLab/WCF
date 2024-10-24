<?php

namespace wcf\system\package\plugin;

use wcf\data\acl\option\ACLOptionEditor;
use wcf\data\acl\option\ACLOptionList;
use wcf\data\acl\option\category\ACLOptionCategory;
use wcf\data\acl\option\category\ACLOptionCategoryEditor;
use wcf\data\acl\option\category\ACLOptionCategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\DOMUtil;

/**
 * This PIP installs, updates or deletes acl options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACLOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $className = ACLOptionEditor::class;

    /**
     * list of loaded acl object type ids sorted by their option type name
     * @var int[]
     */
    protected $optionTypeIDs = [];

    /**
     * @inheritDoc
     */
    public $tableName = 'acl_option';

    /**
     * @inheritDoc
     */
    public $tagName = 'option';

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
            $options[] = [
                'name' => $element->getAttribute('name'),
                'objectType' => $element->getElementsByTagName('objecttype')->item(0)->nodeValue,
            ];
        }

        if (!empty($options)) {
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                    WHERE       optionName = ?
                            AND objectTypeID = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($options as $option) {
                $statement->execute([
                    $option['name'],
                    $this->getObjectTypeID($option['objectType']),
                    $this->installation->getPackageID(),
                ]);
            }
        }

        // delete categories
        $elements = $xpath->query('/ns:data/ns:delete/ns:optioncategory');
        $categories = [];

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $categories[] = [
                'name' => $element->getAttribute('name'),
                'objectType' => $element->getElementsByTagName('objecttype')->item(0)->nodeValue,
            ];
        }

        if (!empty($categories)) {
            // delete options for given categories
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                    WHERE       categoryName = ?
                            AND objectTypeID = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($categories as $category) {
                $statement->execute([
                    $category['name'],
                    $this->getObjectTypeID($category['objectType']),
                    $this->installation->getPackageID(),
                ]);
            }

            // delete categories
            $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "_category
                    WHERE       categoryName = ?
                            AND objectTypeID = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($categories as $category) {
                $statement->execute([
                    $category['name'],
                    $this->getObjectTypeID($category['objectType']),
                    $this->installation->getPackageID(),
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function importCategories(\DOMXPath $xpath)
    {
        $elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $data = ['categoryName' => $element->getAttribute('name')];

            // get child elements
            $children = $xpath->query('child::*', $element);
            foreach ($children as $child) {
                $data[$child->tagName] = $child->nodeValue;
            }

            $this->saveCategory($data);
        }
    }

    /**
     * @inheritDoc
     */
    protected function saveCategory($category)
    {
        if (isset($category['objectTypeID'])) {
            $objectTypeID = $category['objectTypeID'];
        } else {
            $objectTypeID = $this->getObjectTypeID($category['objecttype']);
        }

        if ($this->editedEntry === null) {
            // search existing category
            $sql = "SELECT  categoryID
                    FROM    wcf1_" . $this->tableName . "_category
                    WHERE   categoryName = ?
                        AND objectTypeID = ?
                        AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $category['categoryName'],
                $objectTypeID,
                $this->installation->getPackageID(),
            ]);
            $row = $statement->fetchArray();
            if (!$row) {
                // insert new category
                $sql = "INSERT INTO wcf1_" . $this->tableName . "_category
                                    (packageID, objectTypeID, categoryName)
                        VALUES      (?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $this->installation->getPackageID(),
                    $objectTypeID,
                    $category['categoryName'],
                ]);
            }
        } else {
            $editedData = $this->getElementData($this->editedEntry, true);

            $sql = "SELECT  categoryID
                    FROM    wcf1_" . $this->tableName . "_category
                    WHERE   categoryName = ?
                        AND objectTypeID = ?
                        AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $editedData['categoryName'],
                $editedData['objectTypeID'],
                $this->installation->getPackageID(),
            ]);
            $categoryID = $statement->fetchSingleColumn();

            if ($categoryID === false) {
                throw new \UnexpectedValueException("Cannot find edited acl option category in database.");
            } else {
                (new ACLOptionCategoryEditor(new ACLOptionCategory($categoryID)))->update([
                    'categoryName' => $category['categoryName'],
                    'objectTypeID' => $objectTypeID,
                ]);
            }
        }
    }

    /**
     * Imports options.
     *
     * @param \DOMXPath $xpath
     * @throws  SystemException
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

            $objectTypeID = $this->getObjectTypeID($data['objecttype']);

            // validate category name
            if (isset($data['categoryname'])) {
                $sql = "SELECT  COUNT(categoryID)
                        FROM    wcf1_" . $this->tableName . "_category
                        WHERE   categoryName = ?
                            AND objectTypeID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $data['categoryname'],
                    $objectTypeID,
                ]);

                if (!$statement->fetchSingleColumn()) {
                    throw new SystemException("unknown category '" . $data['categoryname'] . "' for acl object type '" . $data['objecttype'] . "' given");
                }
            }

            $data = [
                'categoryName' => $data['categoryname'] ?? '',
                'optionName' => $element->getAttribute('name'),
                'objectTypeID' => $objectTypeID,
            ];

            $this->saveOption($data, $data['categoryName']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function saveOption($option, $categoryName, $existingOptionID = 0)
    {
        // check for option existence
        $sql = "SELECT  optionID
                FROM    wcf1_" . $this->tableName . "
                WHERE   optionName = ?
                    AND objectTypeID = ?
                    AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $option['optionName'],
            $option['objectTypeID'],
            $this->installation->getPackageID(),
        ]);
        $row = $statement->fetchArray();
        if (!$row) {
            $sql = "INSERT INTO wcf1_" . $this->tableName . "
                                (packageID, objectTypeID, optionName, categoryName)
                    VALUES      (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->installation->getPackageID(),
                $option['objectTypeID'],
                $option['optionName'],
                $categoryName,
            ]);
        } else {
            $sql = "UPDATE  wcf1_" . $this->tableName . "
                    SET     categoryName = ?
                    WHERE   optionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $categoryName,
                $row['optionID'],
            ]);
        }
    }

    /**
     * Returns the object type id of the acl option type with the given name
     * or throws a SystemException if no such option type exists.
     *
     * @param string $optionType
     * @return  int
     * @throws  SystemException
     */
    protected function getObjectTypeID($optionType)
    {
        if (!isset($this->optionTypeIDs[$optionType])) {
            $sql = "SELECT  objectTypeID
                    FROM    wcf1_object_type
                    WHERE   objectType = ?
                        AND definitionID IN (
                                SELECT  definitionID
                                FROM    wcf1_object_type_definition
                                WHERE   definitionName = 'com.woltlab.wcf.acl'
                            )";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([$optionType]);
            $objectTypeID = $statement->fetchSingleColumn();
            if ($objectTypeID === false) {
                throw new SystemException("unknown object type '" . $optionType . "' given");
            }

            $this->optionTypeIDs[$optionType] = $objectTypeID;
        }

        return $this->optionTypeIDs[$optionType];
    }

    /**
     * @inheritDoc
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return 'aclOption.xml';
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return ['objectType'];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        $objectTypes = [];

        $requiredPackageIDs = \array_merge(
            [$this->installation->getPackageID()],
            \array_keys($this->installation->getPackage()->getAllRequiredPackages())
        );

        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.acl') as $objectType) {
            if (\in_array($objectType->packageID, $requiredPackageIDs)) {
                $objectTypes[$objectType->objectType] = $objectType->objectType;
            }
        }

        \asort($objectTypes);

        switch ($this->entryType) {
            case 'categories':
                $nameFormField = TextFormField::create('name')
                    ->label('wcf.acp.pip.aclOption.categories.name')
                    ->description('wcf.acp.pip.aclOption.categories.name.description')
                    ->required()
                    ->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
                        'wcf.acp.pip.aclOption.categories.name',
                        2
                    ));
                break;

            case 'options':
                $nameFormField = TextFormField::create('name')
                    ->label('wcf.acp.pip.aclOption.options.name')
                    ->description('wcf.acp.pip.aclOption.options.name.description')
                    ->required()
                    ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                        if (!\preg_match('~[a-z][A-z]+~', $formField->getValue())) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'format',
                                    'wcf.acp.pip.aclOption.options.name.error.format'
                                )
                            );
                        }
                    }));
                break;

            default:
                throw new \LogicException('Unreachable');
        }

        $entryType = $this->entryType;
        $objectTypeFormField = SingleSelectionFormField::create('objectType')
            ->objectProperty('objecttype')
            ->label('wcf.acp.pip.aclOption.objectType')
            ->description('wcf.acp.pip.aclOption.objectType.' . $this->entryType . '.description')
            ->options($objectTypes, false, false)
            ->required()
            ->addValidator(new FormFieldValidator(
                'nameUniqueness',
                function (SingleSelectionFormField $formField) use ($entryType) {
                    /** @var TextFormField $nameField */
                    $nameField = $formField->getDocument()->getNodeById('name');

                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $nameField->getValue()
                    ) {
                        switch ($entryType) {
                            case 'categories':
                                $categoryList = new ACLOptionCategoryList();
                                $categoryList->getConditionBuilder()->add('categoryName = ?', [
                                    $nameField->getValue(),
                                ]);
                                $categoryList->getConditionBuilder()->add('objectTypeID = ?', [
                                    ObjectTypeCache::getInstance()->getObjectTypeByName(
                                        'com.woltlab.wcf.acl',
                                        $formField->getValue()
                                    )->objectTypeID,
                                ]);

                                if ($categoryList->countObjects() > 0) {
                                    $nameField->addValidationError(
                                        new FormFieldValidationError(
                                            'notUnique',
                                            'wcf.acp.pip.aclOption.' . $entryType . '.name.error.notUnique'
                                        )
                                    );
                                }
                                break;

                            case 'options':
                                $optionList = new ACLOptionList();
                                $optionList->getConditionBuilder()->add('optionName = ?', [
                                    $nameField->getValue(),
                                ]);
                                $optionList->getConditionBuilder()->add('objectTypeID = ?', [
                                    ObjectTypeCache::getInstance()->getObjectTypeByName(
                                        'com.woltlab.wcf.acl',
                                        $formField->getValue()
                                    )->objectTypeID,
                                ]);

                                if ($optionList->countObjects() > 0) {
                                    $nameField->addValidationError(
                                        new FormFieldValidationError(
                                            'notUnique',
                                            'wcf.acp.pip.aclOption.' . $entryType . '.name.error.notUnique'
                                        )
                                    );
                                }
                                break;
                        }
                    }
                }
            ));

        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([$nameFormField, $objectTypeFormField]);

        if ($this->entryType === 'options') {
            $categoryList = new ACLOptionCategoryList();
            $categoryList->getConditionBuilder()->add('packageID IN (?)', [$requiredPackageIDs]);
            $categoryList->sqlOrderBy = 'categoryName ASC';
            $categoryList->readObjects();

            $categories = [];
            foreach ($categoryList as $category) {
                if (!isset($categories[$category->objectTypeID])) {
                    $categories[$category->objectTypeID] = [];
                }

                $categories[$category->objectTypeID][$category->categoryName] = $category->categoryName;
            }

            foreach ($objectTypes as $objectType) {
                $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                    'com.woltlab.wcf.acl',
                    $objectType
                );

                if (isset($categories[$objectTypeID])) {
                    $categoryNameField = SingleSelectionFormField::create('categoryName_' . $objectTypeID)
                        ->objectProperty('categoryname')
                        ->label('wcf.acp.pip.aclOption.options.categoryName')
                        ->description('wcf.acp.pip.aclOption.options.categoryName.description')
                        ->options(['' => 'wcf.global.noSelection'] + $categories[$objectTypeID])
                        ->addDependency(
                            ValueFormFieldDependency::create('objectType')
                                ->field($objectTypeFormField)
                                ->values([$objectType])
                        );

                    $dataContainer->appendChild($categoryNameField);
                }
            }
        }
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
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'packageID' => $this->installation->getPackage()->packageID,
            'objectType' => $element->getElementsByTagName('objecttype')->item(0)->nodeValue,
        ];

        switch ($this->entryType) {
            case 'categories':
                $data['categoryName'] = $element->getAttribute('name');

                break;

            case 'options':
                $data['optionName'] = $element->getAttribute('name');

                $categoryName = $element->getElementsByTagName('categoryname')->item(0);
                if ($categoryName !== null) {
                    $data['categoryname'] = $categoryName->nodeValue;
                } elseif ($saveData) {
                    $data['categoryname'] = '';
                }

                break;
        }

        if (!$saveData) {
            $data['name'] = $element->getAttribute('name');
        } else {
            $objectType = $data['objectType'];
            unset($data['objectType']);

            $data['objectTypeID'] = $this->getObjectTypeID($objectType);
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        $elementData = $this->getElementData($element);

        return \sha1($elementData['objectType'] . '/' . $elementData['name']);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getXsdFilename()
    {
        return 'aclOption';
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'name' => 'wcf.acp.pip.aclOption.' . $this->entryType . '.name',
            'objectType' => 'wcf.acp.pip.aclOption.objectType',
        ]);
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

                $this->appendElementChildren($category, ['objecttype'], $form);

                return $category;

            case 'options':
                $option = $document->createElement('option');
                $option->setAttribute('name', $formData['name']);

                $this->appendElementChildren(
                    $option,
                    [
                        'objecttype',
                        'categoryname' => '',
                    ],
                    $form
                );

                return $option;
        }

        throw new \LogicException('Unreachable');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $deleteElement = parent::prepareDeleteXmlElement($element);

        $deleteElement->appendChild($element->ownerDocument->createElement(
            'objecttype',
            $element->getElementsByTagName('objecttype')->item(0)->nodeValue
        ));

        return $deleteElement;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $name = $element->getAttribute('name');
        $objectType = $element->getElementsByTagName('objecttype')->item(0)->nodeValue;

        switch ($this->entryType) {
            case 'categories':
                // also delete options
                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                        WHERE       categoryName = ?
                                AND objectTypeID = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->getObjectTypeID($objectType),
                    $this->installation->getPackageID(),
                ]);

                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "_category
                        WHERE       categoryName = ?
                                AND objectTypeID = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->getObjectTypeID($objectType),
                    $this->installation->getPackageID(),
                ]);

                break;

            case 'options':
                $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                        WHERE       optionName = ?
                                AND objectTypeID = ?
                                AND packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $name,
                    $this->getObjectTypeID($objectType),
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

            $xpath = new \DOMXPath($element->ownerDocument);
            $xpath->registerNamespace('ns', $element->ownerDocument->documentElement->getAttribute('xmlns'));

            $options = $xpath->query('/ns:data/ns:import/ns:options')->item(0);

            /** @var \DOMElement $option */
            foreach (DOMUtil::getElements($options, 'option') as $option) {
                if ($option->getElementsByTagName('categoryname')->item(0)->nodeValue === $categoryName) {
                    DOMUtil::removeNode($option);
                }
            }
        }
    }
}
