<?php

namespace wcf\system\package\plugin;

use wcf\data\core\object\CoreObjectEditor;
use wcf\data\core\object\CoreObjectList;
use wcf\system\cache\builder\CoreObjectCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Installs, updates and deletes core objects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CoreObjectPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = CoreObjectEditor::class;

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       objectName = ?
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
            'objectName' => $data['elements']['objectname'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   objectName = ?
                    AND packageID = ?";
        $parameters = [
            $data['objectName'],
            $this->installation->getPackageID(),
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function cleanup()
    {
        CoreObjectCacheBuilder::getInstance()->reset();
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

        $dataContainer->appendChild(
            ClassNameFormField::create('objectName')
                ->objectProperty('objectname')
                ->parentClass(SingletonFactory::class)
                ->addValidator(new FormFieldValidator('uniqueness', function (ClassNameFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getElementsByTagName('objectname')->item(0)->nodeValue !== $formField->getValue()
                    ) {
                        $coreObjectList = new CoreObjectList();
                        $coreObjectList->getConditionBuilder()->add('objectName = ?', [$formField->getValue()]);
                        $coreObjectList->getConditionBuilder()->add('packageID IN (?)', [
                            \array_merge(
                                [$this->installation->getPackage()->packageID],
                                \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                            ),
                        ]);

                        if ($coreObjectList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.coreObject.objectName.error.notUnique'
                                )
                            );
                        }
                    }
                }))
        );
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        return [
            'objectName' => $element->getElementsByTagName('objectname')->item(0)->nodeValue,
            'packageID' => $this->installation->getPackage()->packageID,
        ];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return \sha1($element->getElementsByTagName('objectname')->item(0)->nodeValue);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'objectName' => 'wcf.form.field.className',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $coreObject = $document->createElement($this->tagName);

        $this->appendElementChildren($coreObject, ['objectname'], $form);

        return $coreObject;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $coreObject = $element->ownerDocument->createElement($this->tagName);
        $coreObject->setAttribute(
            'name',
            $element->getElementsByTagName('objectname')->item(0)->nodeValue
        );

        return $coreObject;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $name = $element->getElementsByTagName('objectname')->item(0)->nodeValue;

        $this->handleDelete([['attributes' => ['name' => $name]]]);
    }
}
