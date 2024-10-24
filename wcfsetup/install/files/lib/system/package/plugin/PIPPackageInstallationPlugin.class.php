<?php

namespace wcf\system\package\plugin;

use wcf\data\package\installation\plugin\PackageInstallationPluginEditor;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Installs, updates and deletes package installation plugins.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PIPPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = PackageInstallationPluginEditor::class;

    /**
     * @inheritDoc
     */
    public $tagName = 'pip';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       pluginName = ?
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
            'className' => $data['nodeValue'],
            'pluginName' => $data['attributes']['name'],
            'priority' => $this->installation->getPackage()->package == 'com.woltlab.wcf' ? 1 : 0,
        ];
    }

    /**
     * @see \wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return 'packageInstallationPlugin.xml';
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   pluginName = ?
                    AND packageID = ?";
        $parameters = [
            $data['pluginName'],
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
            TextFormField::create('pluginName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.pip.pluginName')
                ->description('wcf.acp.pip.pip.pluginName.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (\preg_match('~^[a-z][A-z]+$~', $formField->getValue()) !== 1) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.pip.pluginName.error.format'
                            )
                        );
                    }
                }))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                    ) {
                        $pipList = new PackageInstallationPluginList();
                        $pipList->getConditionBuilder()->add('pluginName = ?', [$formField->getValue()]);

                        if ($pipList->countObjects()) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'format',
                                    'wcf.acp.pip.pip.pluginName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            ClassNameFormField::create()
                ->required()
                ->implementedInterface(IPackageInstallationPlugin::class),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        return [
            'className' => $element->nodeValue,
            'pluginName' => $element->getAttribute('name'),
            'priority' => $this->installation->getPackage()->package == 'com.woltlab.wcf' ? 1 : 0,
        ];
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
            'pluginName' => 'wcf.acp.pip.pip.pluginName',
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

        $pip = $document->createElement($this->tagName, $data['className']);
        $pip->setAttribute('name', $data['name']);

        return $pip;
    }
}
