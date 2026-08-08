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

    #[\Override]
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

    #[\Override]
    protected function prepareImport(array $data)
    {
        return [
            'className' => $data['nodeValue'],
            'pluginName' => $data['attributes']['name'],
            'priority' => $this->installation->getPackage()->package === 'com.woltlab.wcf' ? 1 : 0,
        ];
    }

    #[\Override]
    public static function getDefaultFilename(): string
    {
        return 'packageInstallationPlugin.xml';
    }

    /**
     * @return mixed[]
     */
    #[\Override]
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

    #[\Override]
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @return void
     * @since   5.2
     */
    #[\Override]
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

                        if ($pipList->countObjects() !== 0) {
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
     * @return array<string, int|string>
     * @since   5.2
     */
    #[\Override]
    protected function fetchElementData(\DOMElement $element, bool $saveData)
    {
        return [
            'className' => $element->nodeValue,
            'pluginName' => $element->getAttribute('name'),
            'priority' => $this->installation->getPackage()->package === 'com.woltlab.wcf' ? 1 : 0,
        ];
    }

    /**
     * @return string
     * @since   5.2
     */
    #[\Override]
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('name');
    }

    /**
     * @return void
     * @since   5.2
     */
    #[\Override]
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'pluginName' => 'wcf.acp.pip.pip.pluginName',
            'className' => 'wcf.form.field.className',
        ]);
    }

    /**
     * @return \DOMElement
     * @since   5.2
     */
    #[\Override]
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $pip = $document->createElement($this->tagName, $data['className']);
        $pip->setAttribute('name', $data['name']);

        return $pip;
    }
}
