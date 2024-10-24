<?php

namespace wcf\system\package\plugin;

use wcf\data\object\type\definition\ObjectTypeDefinitionEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Installs, updates and deletes object type definitions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ObjectTypeDefinitionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = ObjectTypeDefinitionEditor::class;

    /**
     * @inheritDoc
     */
    public $tagName = 'definition';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       definitionName = ?
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
        return [
            'interfaceName' => $data['elements']['interfacename'] ?? '',
            'definitionName' => $data['elements']['name'],
            'categoryName' => $data['elements']['categoryname'] ?? '',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['definitionName'];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   definitionName = ?";
        $parameters = [$data['definitionName']];

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

        $dataContainer->appendChildren([
            TextFormField::create('definitionName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.objectTypeDefinition.definitionName')
                ->description(
                    'wcf.acp.pip.objectTypeDefinition.definitionName.description',
                    ['project' => $this->installation->getProject()]
                )
                ->required()
                ->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
                    'wcf.acp.pip.objectTypeDefinition.definitionName',
                    4
                ))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if ($formField->getValue()) {
                        $objectTypeDefinition = ObjectTypeCache::getInstance()->getDefinitionByName($formField->getValue());

                        // the definition name is not unique if such an object type definition
                        // already exists and (a) a new definition is added or (b) an existing
                        // definition is edited but the new definition name is not the old definition
                        // name so that the existing definition is not the definition currently edited
                        if (
                            $objectTypeDefinition !== null && (
                                $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                                || $this->editedEntry->getElementsByTagName('name')->item(0)->nodeValue !== $formField->getValue()
                            )
                        ) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.objectTypeDefinition.definitionName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            TextFormField::create('interfaceName')
                ->objectProperty('interfacename')
                ->label('wcf.acp.pip.objectTypeDefinition.interfaceName')
                ->description('wcf.acp.pip.objectTypeDefinition.interfaceName.description')
                ->addValidator(new FormFieldValidator('interfaceExists', static function (TextFormField $formField) {
                    if ($formField->getValue() && !\interface_exists($formField->getValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'nonExistent',
                                'wcf.acp.pip.objectTypeDefinition.interfaceName.error.nonExistent'
                            )
                        );
                    }
                })),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'definitionName' => $element->getElementsByTagName('name')->item(0)->nodeValue,
            'packageID' => $this->installation->getPackage()->packageID,
        ];

        $interfaceName = $element->getElementsByTagName('interfacename')->item(0);
        if ($interfaceName) {
            $data['interfaceName'] = $interfaceName->nodeValue;
        } elseif ($saveData) {
            $data['interfaceName'] = '';
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getElementsByTagName('name')->item(0)->nodeValue;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'definitionName' => 'wcf.acp.pip.objectTypeDefinition.definitionName',
            'interfaceName' => 'wcf.acp.pip.objectTypeDefinition.interfaceName',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $definition = $document->createElement($this->tagName);

        $this->appendElementChildren(
            $definition,
            [
                'name',
                'interfacename' => '',
            ],
            $form
        );

        return $definition;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $objectTypeDefinition = $element->ownerDocument->createElement($this->tagName);
        $objectTypeDefinition->setAttribute(
            'name',
            $element->getElementsByTagName('name')->item(0)->nodeValue
        );

        return $objectTypeDefinition;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $this->handleDelete([
            [
                'attributes' => [
                    'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
                ],
            ],
        ]);
    }
}
