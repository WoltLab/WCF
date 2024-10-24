<?php

namespace wcf\system\package\plugin;

use wcf\data\smiley\SmileyEditor;
use wcf\data\smiley\SmileyList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes smilies.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = SmileyEditor::class;

    /**
     * @inheritDoc
     */
    public $tableName = 'smiley';

    /**
     * @inheritDoc
     */
    public $tagName = 'smiley';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       smileyCode = ?
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
    protected function prepareImport(array $data)
    {
        $showOrder = $this->getShowOrder($data['elements']['showorder'] ?? null);

        return [
            'smileyCode' => $data['attributes']['name'],
            'smileyTitle' => $data['elements']['title'],
            'smileyPath' => $data['elements']['path'],
            'smileyPath2x' => $data['elements']['path2x'] ?? '',
            'aliases' => $data['elements']['aliases'] ?? '',
            'showOrder' => $showOrder,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['smileyCode'];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   smileyCode = ?
                    AND packageID = ?";
        $parameters = [
            $data['smileyCode'],
            $this->installation->getPackageID(),
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
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

        $fileValidator = new FormFieldValidator('fileExists', static function (TextFormField $formField) {
            if (!\file_exists(WCF_DIR . $formField->getValue())) {
                $formField->addValidationError(
                    new FormFieldValidationError(
                        'fileDoesNotExist',
                        'wcf.acp.pip.smiley.smileyPath.error.fileDoesNotExist'
                    )
                );
            }
        });

        $smileyList = new SmileyList();
        $smileyList->readObjects();

        $smileyCodes = [];
        foreach ($smileyList as $smiley) {
            $smileyCodes[] = $smiley->smileyCode;
            $smileyCodes = \array_merge($smileyCodes, $smiley->getAliases());
        }

        // add fields
        $dataContainer->appendChildren([
            TextFormField::create('smileyCode')
                ->objectProperty('name')
                ->label('wcf.acp.pip.smiley.smileyCode')
                ->description('wcf.acp.pip.smiley.smileyCode.description')
                ->required()
                ->maximumLength(255)
                ->addValidator(new FormFieldValidator(
                    'uniqueness',
                    function (TextFormField $formField) use ($smileyCodes) {
                        if (
                            $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                            || $this->editedEntry->getAttribute('name') !== $formField->getSaveValue()
                        ) {
                            if (\in_array($formField->getValue(), $smileyCodes)) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'notUnique',
                                        'wcf.acp.pip.smiley.smileyCode.error.notUnique'
                                    )
                                );
                            }
                        }
                    }
                )),

            TitleFormField::create('smileyTitle')
                ->objectProperty('title')
                ->required()
                ->maximumLength(255),

            ItemListFormField::create('aliases')
                ->label('wcf.acp.pip.smiley.aliases')
                ->description('wcf.acp.pip.smiley.aliases.description')
                ->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_NSV)
                ->addValidator(new FormFieldValidator(
                    'uniqueness',
                    function (ItemListFormField $formField) use ($smileyCodes) {
                        if (empty($formField->getValue())) {
                            return;
                        }

                        $aliases = null;
                        if ($this->editedEntry) {
                            $aliases = $this->editedEntry->getElementsByTagName('aliases')->item(0);
                        }

                        if (
                            $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                            || $aliases === null
                            || $aliases->nodeValue !== $formField->getSaveValue()
                        ) {
                            $notUniqueCodes = [];
                            foreach ($formField->getValue() as $alias) {
                                if (\in_array($alias, $smileyCodes)) {
                                    $notUniqueCodes[] = $alias;
                                }
                            }

                            if (!empty($notUniqueCodes)) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'notUnique',
                                        'wcf.acp.pip.smiley.aliases.error.notUnique',
                                        ['notUniqueCodes' => $notUniqueCodes]
                                    )
                                );
                            }
                        }
                    }
                )),

            IntegerFormField::create('showOrder')
                ->objectProperty('showorder')
                ->label('wcf.form.field.showOrder')
                ->description('wcf.acp.pip.smiley.showOrder.description')
                ->nullable(),

            TextFormField::create('smileyPath')
                ->objectProperty('path')
                ->label('wcf.acp.pip.smiley.smileyPath')
                ->description(
                    'wcf.acp.pip.smiley.smileyPath.description',
                    ['path' => WCF_DIR]
                )
                ->required()
                ->maximumLength(255)
                ->addValidator($fileValidator),

            TextFormField::create('smileyPath2x')
                ->objectProperty('path2x')
                ->label('wcf.acp.pip.smiley.smileyPath2x')
                ->description(
                    'wcf.acp.pip.smiley.smileyPath2x.description',
                    ['path' => WCF_DIR]
                )
                ->maximumLength(255)
                ->addValidator($fileValidator),
        ]);

        // ensure proper normalization of template code
        $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'templateCode',
            static function (IFormDocument $document, array $parameters) {
                $parameters['data']['aliases'] = StringUtil::unifyNewlines(
                    StringUtil::escapeCDATA($parameters['data']['aliases'])
                );

                return $parameters;
            }
        ));
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'packageID' => $this->installation->getPackage()->packageID,
            'smileyCode' => $element->getAttribute('name'),
            'smileyPath' => $element->getElementsByTagName('path')->item(0)->nodeValue,
            'smileyTitle' => $element->getElementsByTagName('title')->item(0)->nodeValue,
        ];

        $optionalElements = [
            'aliases' => 'aliases',
            'smileyPath2x' => 'path2x',
        ];
        foreach ($optionalElements as $arrayKey => $elementName) {
            $child = $element->getElementsByTagName($elementName)->item(0);
            if ($child !== null) {
                $data[$arrayKey] = $child->nodeValue;
            } else {
                $data[$arrayKey] = '';
            }
        }

        $showOrder = $element->getElementsByTagName('showorder')->item(0);
        if ($showOrder !== null) {
            $data['showOrder'] = $showOrder->nodeValue;
        }
        if ($saveData && $this->editedEntry === null) {
            // only set explicit showOrder when adding new menu item
            $data['showOrder'] = $this->getShowOrder(
                $data['showOrder'] ?? null
            );
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
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'smileyCode' => 'wcf.acp.pip.smiley.smileyCode',
            'smileyTitle' => 'wcf.global.title',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $smiley = $document->createElement($this->tagName);
        $smiley->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $smiley,
            [
                'title',
                'path',
                'path2x' => '',
                'aliases' => [
                    'cdata' => true,
                    'defaultValue' => '',
                ],
                'showOrder' => null,
            ],
            $form
        );

        return $smiley;
    }
}
