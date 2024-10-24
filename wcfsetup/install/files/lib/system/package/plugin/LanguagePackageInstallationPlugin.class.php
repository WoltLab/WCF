<?php

namespace wcf\system\package\plugin;

use wcf\data\IEditableCachedObject;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\category\LanguageCategoryAction;
use wcf\data\language\item\LanguageItemEditor;
use wcf\data\language\item\LanguageItemList;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\DevtoolsPipEntryList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TMultiXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\SourceCodeFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Installs, updates and deletes languages, their categories and items.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TMultiXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = LanguageItemEditor::class;

    /**
     * newly created language categories when saving language item via GUI
     * @var LanguageCategory[]
     */
    public $newLanguageCategories = [];

    /**
     * @inheritDoc
     */
    public $tableName = 'language_item';

    /**
     * @inheritDoc
     */
    public $tagName = 'item';

    /**
     * @inheritDoc
     */
    public function install()
    {
        AbstractPackageInstallationPlugin::install();

        // get language files
        $languageFiles = [];
        $multipleFiles = false;
        $filename = $this->instruction['value'];
        if (\strpos($filename, '*') !== false) {
            // wildcard syntax; import multiple files
            $multipleFiles = true;
            $files = $this->installation->getArchive()->getTar()->getContentList();
            $pattern = \str_replace("\\*", ".*", \preg_quote($filename));

            foreach ($files as $file) {
                if (\preg_match('!' . $pattern . '!i', $file['filename'])) {
                    if (\preg_match('~([a-z-]+)\.xml$~i', $file['filename'], $match)) {
                        $languageFiles[$match[1]] = $file['filename'];
                    } else {
                        throw new SystemException("Cannot determine language code of language file '" . $file['filename'] . "'");
                    }
                }
            }
        } else {
            if (!empty($this->instruction['attributes']['languagecode'])) {
                $languageCode = $this->instruction['attributes']['languagecode'];
            } elseif (!empty($this->instruction['attributes']['language'])) {
                $languageCode = $this->instruction['attributes']['language'];
            } elseif (\preg_match('~([a-z-]+)\.xml$~i', $filename, $match)) {
                $languageCode = $match[1];
            } else {
                throw new SystemException("Cannot determine language code of language file '" . $filename . "'");
            }

            $languageFiles[$languageCode] = $filename;
        }

        // get installed languages
        $sql = "SELECT      *
                FROM        wcf1_language
                ORDER BY    isDefault DESC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $installedLanguages = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // install language
        foreach ($installedLanguages as $installedLanguage) {
            $languageFile = null;
            $updateExistingItems = true;
            if (isset($languageFiles[$installedLanguage['languageCode']])) {
                $languageFile = $languageFiles[$installedLanguage['languageCode']];
            } elseif ($multipleFiles) {
                // do not update existing items, only add new ones
                $updateExistingItems = false;

                // use default language
                if (isset($languageFiles[$installedLanguages[0]['languageCode']])) {
                    $languageFile = $languageFiles[$installedLanguages[0]['languageCode']];
                } // use english (if installed)
                elseif (isset($languageFiles['en'])) {
                    foreach ($installedLanguages as $installedLanguage2) {
                        if ($installedLanguage2['languageCode'] == 'en') {
                            $languageFile = $languageFiles['en'];
                            break;
                        }
                    }
                }

                // use any installed language
                if ($languageFile === null) {
                    foreach ($installedLanguages as $installedLanguage2) {
                        if (isset($languageFiles[$installedLanguage2['languageCode']])) {
                            $languageFile = $languageFiles[$installedLanguage2['languageCode']];
                            break;
                        }
                    }
                }

                // use first delivered language
                if ($languageFile === null) {
                    foreach ($languageFiles as $languageFile) {
                        break;
                    }
                }
            }

            // save language
            if ($languageFile !== null) {
                if ($xml = $this->readLanguage($languageFile)) {
                    // get language object
                    $languageEditor = new LanguageEditor(new Language(null, $installedLanguage));

                    // import xml
                    // don't update language files if package is an application
                    $languageEditor->updateFromXML(
                        $xml,
                        $this->installation->getPackageID(),
                        !$this->installation->getPackage()->isApplication,
                        $updateExistingItems
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        parent::uninstall();

        // delete language items
        // Get all items and their categories
        // which where installed from this package.
        $sql = "SELECT  languageItemID, languageCategoryID, languageID
                FROM    wcf1_language_item
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
        $itemIDs = [];
        $categoryIDs = [];
        while ($row = $statement->fetchArray()) {
            $itemIDs[] = $row['languageItemID'];

            // Store categories
            $categoryIDs[$row['languageCategoryID']] = true;
        }

        if (!empty($itemIDs)) {
            $sql = "DELETE FROM wcf1_" . $this->tableName . "
                    WHERE       languageItemID = ?
                            AND packageID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($itemIDs as $itemID) {
                $statement->execute([
                    $itemID,
                    $this->installation->getPackageID(),
                ]);
            }

            $this->deleteEmptyCategories(\array_keys($categoryIDs), $this->installation->getPackageID());
        }
    }

    /**
     * Extracts the language file and parses it. If the specified language file
     * was not found, an exception message is thrown.
     *
     * @param string $filename
     * @return  XML
     * @throws  SystemException
     */
    protected function readLanguage($filename)
    {
        // search language files in package archive
        // throw error message if not found
        if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($filename)) === false) {
            throw new SystemException("language file '" . $filename . "' not found.");
        }

        // extract language file and parse with DOMDocument
        $xml = new XML();
        $xml->loadXML($filename, $this->installation->getArchive()->getTar()->extractToString($fileIndex));

        return $xml;
    }

    /**
     * Deletes categories which where changed by an update or uninstallation
     * in case they are now empty.
     *
     * @param array $categoryIDs
     * @param int $packageID
     */
    protected function deleteEmptyCategories(array $categoryIDs, $packageID)
    {
        // Get empty categories which where changed by this package.
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("language_category.languageCategoryID IN (?)", [$categoryIDs]);

        $sql = "SELECT      COUNT(item.languageItemID) AS count,
                            language_category.languageCategoryID,
                            language_category.languageCategory
                FROM        wcf1_language_category language_category
                LEFT JOIN   wcf1_language_item item
                ON          item.languageCategoryID = language_category.languageCategoryID
                {$conditions}
                GROUP BY    language_category.languageCategoryID ASC,
                            language_category.languageCategory ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $categoriesToDelete = [];
        while ($row = $statement->fetchArray()) {
            if ($row['count'] == 0) {
                $categoriesToDelete[$row['languageCategoryID']] = $row['languageCategory'];
            }
        }

        // Delete categories from DB.
        if (!empty($categoriesToDelete)) {
            $sql = "DELETE FROM wcf1_language_category
                    WHERE       languageCategory = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($categoriesToDelete as $category) {
                $statement->execute([$category]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    protected function postImport()
    {
        LanguageFactory::getInstance()->deleteLanguageCache();
    }

    /**
     * @see \wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return 'language/*.xml';
    }

    /**
     * @inheritDoc
     */
    public static function isValid(PackageArchive $packageArchive, $instruction)
    {
        return true;
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
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        // add fields
        $dataContainer->appendChildren([
            RadioButtonFormField::create('languageCategoryIDMode')
                ->label('wcf.acp.language.item.languageCategoryID.mode')
                ->options([
                    'automatic' => 'wcf.acp.language.item.languageCategoryID.mode.automatic',
                    'selection' => 'wcf.acp.language.item.languageCategoryID.mode.selection',
                    'new' => 'wcf.acp.language.item.languageCategoryID.mode.new',
                ])
                ->value('automatic'),

            SingleSelectionFormField::create('languageCategoryID')
                ->label('wcf.acp.language.item.languageCategoryID')
                ->description('wcf.acp.language.item.languageCategoryID.description')
                ->options(static function () {
                    $categories = [];

                    foreach (LanguageFactory::getInstance()->getCategories() as $languageCategory) {
                        $categories[$languageCategory->languageCategoryID] = $languageCategory->getTitle();
                    }

                    \asort($categories);

                    return $categories;
                }, false, false)
                ->filterable()
                ->addDependency(
                    ValueFormFieldDependency::create('languageCategoryIDMode')
                        ->fieldId('languageCategoryIDMode')
                        ->values(['selection'])
                ),

            TextFormField::create('languageCategory')
                ->label('wcf.acp.language.item.languageCategoryID')
                ->description('wcf.acp.language.item.languageCategory.description')
                ->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
                    'wcf.acp.language.item.languageCategory',
                    2,
                    3
                ))
                ->addValidator(new FormFieldValidator('uniqueness', static function (TextFormField $formField) {
                    if (LanguageFactory::getInstance()->getCategory($formField->getSaveValue()) !== null) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'notUnique',
                                'wcf.acp.language.item.languageCategory.error.notUnique'
                            )
                        );
                    }
                }))
                ->addDependency(
                    ValueFormFieldDependency::create('languageCategoryIDMode')
                        ->fieldId('languageCategoryIDMode')
                        ->values(['new'])
                ),

            TextFormField::create('languageItem')
                ->label('wcf.acp.language.item.languageItem')
                ->description('wcf.acp.language.item.languageItem.description')
                ->required()
                ->maximumLength(191)
                ->addValidator(FormFieldValidatorUtil::getRegularExpressionValidator(
                    '^[A-z0-9-_]+(\.[A-z0-9-_]+){2,}$',
                    'wcf.acp.language.item.languageItem'
                ))
                ->addValidator(new FormFieldValidator('languageCategory', static function (TextFormField $formField) {
                    /** @var RadioButtonFormField $languageCategoryIDMode */
                    $languageCategoryIDMode = $formField->getDocument()->getNodeById('languageCategoryIDMode');

                    switch ($languageCategoryIDMode->getSaveValue()) {
                        case 'automatic':
                            $languageItemPieces = \explode('.', $formField->getSaveValue());

                            $category = LanguageFactory::getInstance()->getCategory(
                                $languageItemPieces[0] . '.' . $languageItemPieces[1] . '.' . $languageItemPieces[2]
                            );
                            if ($category === null) {
                                $category = LanguageFactory::getInstance()->getCategory(
                                    $languageItemPieces[0] . '.' . $languageItemPieces[1]
                                );
                            }

                            if ($category === null) {
                                $languageCategoryIDMode->addValidationError(
                                    new FormFieldValidationError(
                                        'automatic',
                                        'wcf.acp.language.item.languageCategoryID.mode.error.automaticImpossible'
                                    )
                                );
                            }

                            break;

                        case 'selection':
                            /** @var SingleSelectionFormField $languageCategoryID */
                            $languageCategoryID = $formField->getDocument()->getNodeById('languageCategoryID');

                            $languageCategory = LanguageFactory::getInstance()->getCategoryByID($languageCategoryID->getSaveValue());

                            if (\strpos($formField->getSaveValue(), $languageCategory->languageCategory . '.') !== 0) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'prefixMismatch',
                                        'wcf.acp.language.item.languageItem.error.prefixMismatch'
                                    )
                                );
                            }

                            break;

                        case 'new':
                            /** @var TextFormField $languageCategory */
                            $languageCategory = $formField->getDocument()->getNodeById('languageCategory');

                            if (\strpos($formField->getSaveValue(), $languageCategory->getSaveValue() . '.') !== 0) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'prefixMismatch',
                                        'wcf.acp.language.item.languageItem.error.prefixMismatch'
                                    )
                                );
                            }

                            break;

                        default:
                            throw new \LogicException("Unknown language category mode '{$languageCategoryIDMode->getSaveValue()}'.");
                    }
                }))
                ->addValidator(
                    new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                        if (
                            $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                            || $this->editedEntries[0]->getAttribute('name') !== $formField->getSaveValue()
                        ) {
                            $languageItemList = new LanguageItemList();
                            $languageItemList->getConditionBuilder()->add(
                                'languageItem = ?',
                                [$formField->getSaveValue()]
                            );

                            if ($languageItemList->countObjects() > 0) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'notUnique',
                                        'wcf.acp.language.item.languageItem.error.notUnique'
                                    )
                                );
                            }
                        }
                    })
                ),
        ]);

        // add one field per language
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $description = null;
            $descriptionLanguageItem = 'wcf.acp.pip.language.languageItemValue.' . $language->languageCode . '.description';
            if (WCF::getLanguage()->get($descriptionLanguageItem, true) !== '') {
                $description = $descriptionLanguageItem;
            }

            $dataContainer->appendChild(
                SourceCodeFormField::create($language->languageCode)
                    ->label($language->languageName)
                    ->description($description)
                    ->language('smartymixed')
            );
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'languageID' => LanguageFactory::getInstance()->getLanguageByCode($element->ownerDocument->documentElement->getAttribute('languagecode'))->languageID,
            'languageItem' => $element->getAttribute('name'),
            'languageItemValue' => $element->nodeValue,
            'languageItemOriginIsSystem' => 1,
            'packageID' => $this->installation->getPackage()->packageID,
        ];

        if ($element->parentNode) {
            $languageCategory = $element->parentNode->getAttribute('name');

            if ($saveData) {
                if (isset($this->newLanguageCategories[$languageCategory])) {
                    $data['languageCategoryID'] = $this->newLanguageCategories[$languageCategory]->languageCategoryID;
                } else {
                    $languageCategoryObject = LanguageFactory::getInstance()->getCategory($languageCategory);
                    if ($languageCategoryObject !== null) {
                        $data['languageCategoryID'] = $languageCategoryObject->languageCategoryID;
                    } else {
                        // if a new language category should be created, pass the name
                        // instead of the id
                        $data['languageCategory'] = $languageCategory;
                    }
                }
            } else {
                $data['languageCategoryID'] = LanguageFactory::getInstance()->getCategory($languageCategory)->languageCategoryID;
            }
        }

        if (!$saveData) {
            $data[$element->ownerDocument->documentElement->getAttribute('languagecode')] = $element->nodeValue;
            $data['languageCategoryIDMode'] = 'selection';
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
     * Returns a list of all pip entries of this pip.
     *
     * @return  IDevtoolsPipEntryList
     */
    public function getEntryList()
    {
        $entryList = new DevtoolsPipEntryList();
        $this->setEntryListKeys($entryList);

        $entryData = [];
        foreach ($this->getProjectXmls() as $xml) {
            $xpath = $xml->xpath();
            $languageCode = $xml->getDocument()->documentElement->getAttribute('languagecode');

            /** @var \DOMElement $element */
            foreach ($this->getImportElements($xpath) as $element) {
                $elementIdentifier = $this->getElementIdentifier($element);

                if (!isset($entryData[$elementIdentifier])) {
                    $entryData[$elementIdentifier] = [
                        'languageItem' => $element->getAttribute('name'),
                        'languageItemCategory' => $element->parentNode->getAttribute('name'),
                        $languageCode => 1,
                    ];
                } else {
                    $entryData[$elementIdentifier][$languageCode] = 1;
                }
            }
        }

        // re-sort language items as missing language items in first processed language
        // can cause non-sorted entries even in each language file is sorted
        \uasort($entryData, static function (array $item1, array $item2) {
            return $item1['languageItem'] <=> $item2['languageItem'];
        });

        foreach ($entryData as $identifier => $data) {
            foreach ($entryList->getKeys() as $key => $label) {
                if (!isset($data[$key])) {
                    $data[$key] = 0;
                }
            }

            $entryList->addEntry($identifier, $data);
        }

        return $entryList;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getImportElements(\DOMXPath $xpath)
    {
        return $xpath->query('/ns:language/ns:import/ns:category/ns:item');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getEmptyXml($languageCode)
    {
        $xsdFilename = $this->getXsdFilename();

        $language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
        if ($language === null) {
            throw new \InvalidArgumentException("Unknown language code '{$languageCode}'.");
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/6.0/{$xsdFilename}.xsd" languagecode="{$language->languageCode}" languagename="{$language->languageName}" countrycode="{$language->countryCode}">
</language>
XML;
    }

    /**
     * Returns the xml objects for this pip.
     *
     * @param bool $createXmlFiles if `true` and if a relevant XML file does not exist, it is created
     * @return  XML[]
     */
    protected function getProjectXmls($createXmlFiles = false)
    {
        $xmls = [];

        if ($createXmlFiles) {
            $directory = $this->installation->getProject()->path . ($this->installation->getProject()->isCore() ? 'wcfsetup/install/lang/' : 'language/');
            if (!\is_dir($directory)) {
                FileUtil::makePath($directory);
            }

            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $languageFile = $directory . $language->languageCode . '.xml';

                $xml = new XML();
                if (!\file_exists($languageFile)) {
                    $xml->loadXML($languageFile, $this->getEmptyXml(\substr(\basename($languageFile), 0, -4)));
                } else {
                    $xml->load($languageFile);
                }

                $xmls[] = $xml;
            }
        } else {
            foreach ($this->installation->getProject()->getLanguageFiles() as $languageFile) {
                $xml = new XML();
                $xml->load($languageFile);

                // only consider installed languages
                $languageCode = $xml->getDocument()->documentElement->getAttribute('languagecode');
                if (LanguageFactory::getInstance()->getLanguageByCode($languageCode) !== null) {
                    $xmls[] = $xml;
                }
            }
        }

        return $xmls;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function saveObject(\DOMElement $newElement, ?\DOMElement $oldElement = null)
    {
        $newElementData = $this->getElementData($newElement, true);

        $existingRow = [];
        if ($oldElement !== null) {
            $sql = "SELECT  *
                    FROM    wcf1_language_item
                    WHERE   languageItem = ?
                        AND languageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $oldElement->getAttribute('name'),
                // use new element as old element has no access to parent element anymore
                LanguageFactory::getInstance()->getLanguageByCode(
                    $newElement->ownerDocument->documentElement->getAttribute('languagecode')
                )->languageID,
            ]);

            $existingRow = $statement->fetchArray();
            if (!$existingRow) {
                $existingRow = [];
            }
        }

        if (!isset($newElementData['languageCategoryID']) && isset($newElementData['languageCategory'])) {
            /** @var LanguageCategory $languageCategory */
            $languageCategory = (new LanguageCategoryAction([], 'create', [
                'data' => [
                    'languageCategory' => $newElementData['languageCategory'],
                ],
            ]))->executeAction()['returnValues'];

            $this->newLanguageCategories[$languageCategory->languageCategory] = $languageCategory;

            $newElementData['languageCategoryID'] = $languageCategory->languageCategoryID;
            unset($newElementData['languageCategory']);

            LanguageFactory::getInstance()->clearCache();
        }

        $this->import($existingRow, $newElementData);

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
        $keys = [
            'languageItem' => 'wcf.acp.language.item.languageItem',
            'languageItemCategory' => 'wcf.acp.language.item.languageCategoryID',
        ];

        foreach ($this->getProjectXmls() as $xml) {
            $keys[$xml->getDocument()->documentElement->getAttribute('languagecode')] = $xml->getDocument()->documentElement->getAttribute('languagecode');
        }

        $entryList->setKeys($keys);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $languageCode = $document->documentElement->getAttribute('languagecode');
        $languageItemValue = $data[$languageCode];

        $languageItem = $document->createElement($this->tagName);
        $languageItem->setAttribute('name', $data['languageItem']);
        $languageItem->appendChild($document->createCDATASection(StringUtil::escapeCDATA($languageItemValue)));

        // language category
        $languageCategoryName = null;
        switch ($data['languageCategoryIDMode']) {
            case 'automatic':
                $languageItemPieces = \explode('.', $data['languageItem']);

                $category = LanguageFactory::getInstance()->getCategory(
                    $languageItemPieces[0] . '.' . $languageItemPieces[1] . '.' . $languageItemPieces[2]
                );
                if ($category === null) {
                    $category = LanguageFactory::getInstance()->getCategory(
                        $languageItemPieces[0] . '.' . $languageItemPieces[1]
                    );
                }

                if ($category === null) {
                    throw new \UnexpectedValueException("Cannot determine language item category for language item '{$data['languageItem']}'.");
                }

                $languageCategoryName = $category->languageCategory;

                break;

            case 'new':
                $languageCategoryName = $data['languageCategory'];

                break;

            case 'selection':
                $languageCategoryName = LanguageFactory::getInstance()->getCategoryByID($data['languageCategoryID'])->languageCategory;

                break;

            default:
                throw new \LogicException("Unknown language category mode '{$data['languageCategoryIDMode']}'.");
        }

        /** @var \DOMElement $import */
        $import = $document->getElementsByTagName('import')->item(0);
        if ($import === null) {
            $import = $document->createElement('import');
            DOMUtil::prepend($import, $document->documentElement);
        }

        /** @var \DOMElement $languageCategory */
        foreach ($import->getElementsByTagName('category') as $languageCategory) {
            if ($languageCategory instanceof \DOMElement && $languageCategory->getAttribute('name') === $languageCategoryName) {
                $languageCategory->appendChild($languageItem);
                break;
            }
        }

        if ($languageItem->parentNode === null) {
            $languageCategory = $document->createElement('category');
            $languageCategory->setAttribute('name', $languageCategoryName);
            $languageCategory->appendChild($languageItem);

            $import->appendChild($languageCategory);
        }

        return $languageItem;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function createAndInsertNewXmlElement(XML $xml, IFormDocument $form)
    {
        return $this->createXmlElement($xml->getDocument(), $form);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function replaceXmlElement(XML $xml, IFormDocument $form, $identifier)
    {
        $newElement = $this->createXmlElement($xml->getDocument(), $form);

        // replace old element
        $element = $this->getElementByIdentifier($xml, $identifier);
        if ($element !== null) {
            if ($element->parentNode === $newElement->parentNode) {
                DOMUtil::replaceElement($element, $newElement, false);
            } else {
                DOMUtil::removeNode($element);
            }
        }

        return $newElement;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $sql = "DELETE FROM wcf1_language_item
                WHERE       languageItem = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $element->getAttribute('name'),
            $this->installation->getPackageID(),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function sanitizeXmlFileAfterDeleteEntry(\DOMDocument $document)
    {
        $language = $document->getElementsByTagName('language')->item(0);

        foreach (DOMUtil::getElements($language, 'category') as $category) {
            if ($category->childNodes->length === 0) {
                DOMUtil::removeNode($category);
            }
        }

        return $language->childNodes->length === 0;
    }
}
