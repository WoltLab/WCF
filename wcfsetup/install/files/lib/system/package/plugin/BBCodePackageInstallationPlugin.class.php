<?php

namespace wcf\system\package\plugin;

use wcf\data\bbcode\attribute\BBCodeAttributeEditor;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeEditor;
use wcf\data\bbcode\BBCodeList;
use wcf\data\package\PackageCache;
use wcf\system\bbcode\IBBCode;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\VoidFormDataProcessor;
use wcf\system\form\builder\field\bbcode\BBCodeAttributesFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IconFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes bbcodes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = BBCodeEditor::class;

    /**
     * @inheritDoc
     */
    public $tableName = 'bbcode';

    /**
     * @inheritDoc
     */
    public $tagName = 'bbcode';

    /**
     * list of attributes per bbcode id
     * @var mixed[][]
     */
    protected $attributes = [];

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       bbcodeTag = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $item['attributes']['name'],
                $this->installation->getPackageID(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element)
    {
        $nodeValue = $element->nodeValue;

        // read pages
        if ($element->tagName == 'attributes') {
            $nodeValue = [];

            $attributes = $xpath->query('child::*', $element);
            /** @var \DOMElement $attribute */
            foreach ($attributes as $attribute) {
                $attributeNo = $attribute->getAttribute('name');
                $nodeValue[$attributeNo] = [];

                $attributeValues = $xpath->query('child::*', $attribute);
                foreach ($attributeValues as $attributeValue) {
                    $nodeValue[$attributeNo][$attributeValue->tagName] = $attributeValue->nodeValue;
                }
            }
        } elseif ($element->tagName === 'wysiwygicon' && !\str_contains($element->nodeValue, '.')) {
            $solid = $element->getAttribute('solid');
            $nodeValue = \sprintf(
                "%s;%s",
                $element->nodeValue,
                $solid === 'true' ? 'true' : 'false'
            );
        }

        $elements[$element->tagName] = $nodeValue;
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $data = [
            'bbcodeTag' => \mb_strtolower(StringUtil::trim($data['attributes']['name'])),
            'htmlOpen' => !empty($data['elements']['htmlopen']) ? $data['elements']['htmlopen'] : '',
            'htmlClose' => !empty($data['elements']['htmlclose']) ? $data['elements']['htmlclose'] : '',
            'wysiwygIcon' => !empty($data['elements']['wysiwygicon']) ? $data['elements']['wysiwygicon'] : '',
            'attributes' => $data['elements']['attributes'] ?? [],
            'className' => !empty($data['elements']['classname']) ? $data['elements']['classname'] : '',
            'isBlockElement' => !empty($data['elements']['isBlockElement']) ? 1 : 0,
            'isSourceCode' => !empty($data['elements']['sourcecode']) ? 1 : 0,
            'buttonLabel' => $data['elements']['buttonlabel'] ?? '',
            'originIsSystem' => 1,
        ];

        if ($data['wysiwygIcon'] && $data['buttonLabel']) {
            $data['showButton'] = 1;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['bbcodeTag'];
    }

    /**
     * @inheritDoc
     */
    protected function validateImport(array $data)
    {
        // check if bbcode tag already exists
        $sqlData = $this->findExistingItem($data);
        $statement = WCF::getDB()->prepare($sqlData['sql']);
        $statement->execute($sqlData['parameters']);
        $row = $statement->fetchArray();
        if ($row && $row['packageID'] != $this->installation->getPackageID()) {
            $package = PackageCache::getInstance()->getPackage($row['packageID']);
            throw new SystemException("BBCode '" . $data['bbcodeTag'] . "' is already provided by '" . $package . "' ('" . $package->package . "').");
        }
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   bbcodeTag = ?";
        $parameters = [$data['bbcodeTag']];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function import(array $row, array $data)
    {
        // extract attributes
        $attributes = $data['attributes'];
        unset($data['attributes']);

        if (!empty($row)) {
            // allow updating of all values except for those controlling the editor button
            unset($data['wysiwygIcon']);
            unset($data['buttonLabel']);
            unset($data['showButton']);
        }

        /** @var BBCode $bbcode */
        $bbcode = parent::import($row, $data);

        // store attributes for later import
        $this->attributes[$bbcode->bbcodeID] = $attributes;

        return $bbcode;
    }

    /**
     * @inheritDoc
     */
    protected function postImport()
    {
        $condition = new PreparedStatementConditionBuilder();
        $condition->add('bbcodeID IN (?)', [\array_keys($this->attributes)]);

        // clear attributes
        $sql = "DELETE FROM wcf1_bbcode_attribute
                {$condition}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($condition->getParameters());

        if (!empty($this->attributes)) {
            foreach ($this->attributes as $bbcodeID => $bbcodeAttributes) {
                if ($bbcodeID != \intval($bbcodeID)) {
                    $bbcodeID = BBCode::getBBCodeByTag($bbcodeID)->bbcodeID;
                }

                foreach ($bbcodeAttributes as $attributeNo => $attribute) {
                    BBCodeAttributeEditor::create([
                        'bbcodeID' => $bbcodeID,
                        'attributeNo' => $attributeNo,
                        'attributeHtml' => !empty($attribute['html']) ? $attribute['html'] : '',
                        'validationPattern' => !empty($attribute['validationpattern']) ? $attribute['validationpattern'] : '',
                        'required' => !empty($attribute['required']) ? $attribute['required'] : 0,
                        'useText' => !empty($attribute['usetext']) ? $attribute['usetext'] : 0,
                    ]);
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return 'bbcode.xml';
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
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'attributes' => [],
            'bbcodeTag' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
            'originIsSystem' => 1,
        ];

        $optionalElements = [
            'buttonLabel' => 'buttonlabel',
            'className' => 'classname',
            'htmlClose' => 'htmlclose',
            'htmlOpen' => 'htmlopen',
            'isBlockElement' => 'isBlockElement',
            'isSourceCode' => 'sourcecode',
            'wysiwygicon' => 'wysiwygicon',
        ];
        foreach ($optionalElements as $arrayKey => $elementName) {
            $child = $element->getElementsByTagName($elementName)->item(0);
            if ($child !== null) {
                $data[$arrayKey] = $child->nodeValue;
            } elseif ($saveData) {
                if (\substr($arrayKey, 0, 2) === 'is') {
                    $data[$arrayKey] = 0;
                } else {
                    $data[$arrayKey] = '';
                }
            }
        }

        if (!empty($data['wysiwygicon']) && !empty($data['buttonLabel'])) {
            $data['showButton'] = 1;
        } elseif ($saveData) {
            $data['showButton'] = 0;
        }

        // attributes
        $attributes = $element->getElementsByTagName('attributes')->item(0);
        if ($attributes !== null) {
            $optionalAttributeElements = [
                'html' => 'attributeHtml',
                'required' => 'required',
                'usetext' => $saveData ? 'usetext' : 'useText',
                'validationpattern' => $saveData ? 'validationpattern' : 'validationPattern',
            ];

            /** @var \DOMElement $attribute */
            foreach ($attributes->childNodes as $attribute) {
                $attributeData = [];

                foreach ($optionalAttributeElements as $elementName => $arrayIndex) {
                    $child = $attribute->getElementsByTagName($elementName)->item(0);
                    if ($child !== null) {
                        $attributeData[$arrayIndex] = $child->nodeValue;
                    } elseif ($saveData) {
                        if ($elementName === 'required' || $elementName === 'usetext') {
                            $attributeData[$arrayIndex] = 0;
                        } else {
                            $attributeData[$arrayIndex] = '';
                        }
                    }
                }

                $data['attributes'][] = $attributeData;
            }
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
    protected function getXsdFilename()
    {
        return 'bbcode';
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        // shared validators
        $htmlValidator = new FormFieldValidator('format', static function (TextFormField $formField) {
            $value = $formField->getValue();
            if ($value !== null && $value !== '') {
                if (\in_array(\substr($formField->getValue(), 0, 1), ['<', '>'])) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'leadingBracket',
                            'wcf.acp.pip.bbcode.htmlOpen.error.leadingBracket'
                        )
                    );
                } elseif (\in_array(\substr($formField->getValue(), -1, 1), ['<', '>'])) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'trailingBracket',
                            'wcf.acp.pip.bbcode.htmlOpen.error.trailingBracket'
                        )
                    );
                }
            }
        });

        // add fields
        $dataContainer->appendChildren([
            TextFormField::create('bbcodeTag')
                ->objectProperty('name')
                ->label('wcf.acp.pip.bbcode.bbcodeTag')
                ->description('wcf.acp.pip.bbcode.bbcodeTag.description')
                ->required()
                ->maximumLength(191)
                ->addValidator(FormFieldValidatorUtil::getRegularExpressionValidator(
                    '[a-z0-9]+',
                    'wcf.acp.pip.bbcode.bbcodeTag'
                ))
                ->addValidator(new FormFieldValidator('allNone', static function (TextFormField $formField) {
                    if ($formField->getValue() === 'all' || $formField->getValue() === 'none') {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'allNone',
                                'wcf.acp.pip.bbcode.bbcodeTag.error.allNone'
                            )
                        );
                    }
                }))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                    ) {
                        $providerList = new BBCodeList();
                        $providerList->getConditionBuilder()->add('bbcodeTag = ?', [$formField->getValue()]);

                        if ($providerList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.bbcode.bbcodeTag.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            TextFormField::create('htmlOpen')
                ->objectProperty('htmlopen')
                ->label('wcf.acp.pip.bbcode.htmlOpen')
                ->description('wcf.acp.pip.bbcode.htmlOpen.description')
                ->maximumLength(255)
                ->addValidator($htmlValidator),

            TextFormField::create('htmlClose')
                ->objectProperty('htmlclose')
                ->label('wcf.acp.pip.bbcode.htmlClose')
                ->description('wcf.acp.pip.bbcode.htmlClose.description')
                ->maximumLength(255)
                // note: the language items for the opening tag are reused
                ->addValidator($htmlValidator),

            BooleanFormField::create('isBlockElement')
                ->label('wcf.acp.pip.bbcode.isBlockElement')
                ->description('wcf.acp.pip.bbcode.isBlockElement.description'),

            BooleanFormField::create('isSourceCode')
                ->objectProperty('sourcecode')
                ->label('wcf.acp.pip.bbcode.isSourceCode')
                ->description('wcf.acp.pip.bbcode.isSourceCode.description'),

            ClassNameFormField::create()
                ->objectProperty('classname')
                ->implementedInterface(IBBCode::class),

            BooleanFormField::create('showButton')
                ->objectProperty('showbutton')
                ->label('wcf.acp.pip.bbcode.showButton')
                ->description('wcf.acp.pip.bbcode.showButton.description'),

            TextFormField::create('buttonLabel')
                ->objectProperty('buttonlabel')
                ->label('wcf.acp.pip.bbcode.buttonLabel')
                ->description('wcf.acp.pip.bbcode.buttonLabel.description')
                ->required()
                ->maximumLength(255)
                ->addDependency(
                    NonEmptyFormFieldDependency::create('showButton')
                        ->fieldId('showButton')
                ),

            RadioButtonFormField::create('iconType')
                ->label('wcf.acp.pip.bbcode.iconType')
                ->options([
                    'filePath' => 'wcf.acp.pip.bbcode.iconType.filePath',
                    'fontAwesome' => 'wcf.acp.pip.bbcode.iconType.fontAwesome',
                ])
                ->required()
                ->value('fontAwesome')
                ->addDependency(
                    NonEmptyFormFieldDependency::create('showButton')
                        ->fieldId('showButton')
                ),

            TextFormField::create('iconPath')
                ->objectProperty('wysiwygicon')
                ->label('wcf.acp.pip.bbcode.iconPath')
                ->description(
                    'wcf.acp.pip.bbcode.iconPath.description',
                    ['path' => WCF_DIR . 'icon/']
                )
                ->required()
                ->maximumLength(255)
                ->addValidator(new FormFieldValidator('fileExists', static function (TextFormField $formField) {
                    if (!\file_exists(WCF_DIR . 'icon/' . $formField->getValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'fileDoesNotExist',
                                'wcf.acp.pip.bbcode.iconPath.error.fileDoesNotExist'
                            )
                        );
                    }
                }))
                ->addDependency(
                    ValueFormFieldDependency::create('iconType')
                        ->fieldId('iconType')
                        ->values(['filePath'])
                ),

            IconFormField::create('fontAwesomeIcon')
                ->objectProperty('wysiwygicon')
                ->label('wcf.acp.pip.bbcode.wysiwygIcon')
                ->required()
                ->addDependency(
                    ValueFormFieldDependency::create('iconType')
                        ->fieldId('iconType')
                        ->values(['fontAwesome'])
                ),
        ]);

        $form->appendChild(
            FormContainer::create('bbcodeAttributes')
                ->attribute('data-ignore-dependencies', 1)
                ->label('wcf.acp.pip.bbcode.attributes')
                ->appendChild(
                    BBCodeAttributesFormField::create()
                )
        );

        // discard the `iconType` value as it is only used to distinguish the two icon input fields
        $form->getDataHandler()->addProcessor(new VoidFormDataProcessor('iconType'));
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'bbcodeTag' => 'wcf.acp.pip.bbcode.bbcodeTag',
            'htmlOpen' => 'wcf.acp.pip.bbcode.htmlOpen',
            'className' => 'wcf.form.field.className',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $bbcode = $document->createElement($this->tagName);
        $bbcode->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $bbcode,
            [
                'classname' => '',
                'htmlopen' => [
                    'cdata' => true,
                    'defaultValue' => '',
                ],
                'htmlclose' => [
                    'cdata' => true,
                    'defaultValue' => '',
                ],
                'isBlockElement' => 0,
                'sourcecode' => 0,
                'buttonlabel' => '',
                'wysiwygicon' => '',
            ],
            $form
        );

        if (!empty($data['attributes'])) {
            $attributes = $document->createElement('attributes');
            $bbcode->appendChild($attributes);

            foreach ($data['attributes'] as $attributeNumber => $attributeData) {
                $attribute = $document->createElement('attribute');
                $attribute->setAttribute('name', (string)$attributeNumber);

                if (!empty($attributeData['attributeHtml'])) {
                    $html = $document->createElement('html');
                    $html->appendChild($document->createCDATASection($attributeData['attributeHtml']));
                    $attribute->appendChild($html);
                }
                if (!empty($attributeData['validationPattern'])) {
                    $validationpattern = $document->createElement('validationpattern');
                    $validationpattern->appendChild($document->createCDATASection($attributeData['validationPattern']));
                    $attribute->appendChild($validationpattern);
                }
                if (!empty($attributeData['required'])) {
                    $attribute->appendChild($document->createElement('required', $attributeData['required']));
                }
                if (!empty($attributeData['useText'])) {
                    $attribute->appendChild($document->createElement('usetext', $attributeData['useText']));
                }

                $attributes->appendChild($attribute);
            }
        }

        return $bbcode;
    }
}
