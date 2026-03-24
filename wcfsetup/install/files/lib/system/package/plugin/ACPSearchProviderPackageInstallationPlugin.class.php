<?php

namespace wcf\system\package\plugin;

use wcf\data\acp\search\provider\ACPSearchProviderEditor;
use wcf\data\acp\search\provider\ACPSearchProviderList;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\search\acp\IACPSearchResultProvider;
use wcf\system\WCF;

/**
 * Installs, updates and deletes ACP search providers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPSearchProviderPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = ACPSearchProviderEditor::class;

    #[\Override]
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       providerName = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($items as $item) {
            $statement->execute([
                $item['attributes']['name'],
                $this->installation->getPackageID(),
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    #[\Override]
    protected function prepareImport(array $data)
    {
        // get show order
        $showOrder = $data['elements']['showorder'] ?? null;
        $showOrder = $this->getShowOrder($showOrder);

        return [
            'className' => $data['elements']['classname'],
            'providerName' => $data['attributes']['name'],
            'showOrder' => $showOrder,
        ];
    }

    #[\Override]
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   providerName = ?
                    AND packageID = ?";
        $parameters = [
            $data['providerName'],
            $this->installation->getPackageID(),
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    #[\Override]
    protected function cleanup()
    {
        ACPSearchProviderCacheBuilder::getInstance()->reset();
    }

    #[\Override]
    public function getNameByData(array $data): string
    {
        return $data['providerName'];
    }

    /**
     * @see \wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
     */
    #[\Override]
    public static function getDefaultFilename()
    {
        return 'acpSearchProvider.xml';
    }

    #[\Override]
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @param bool $saveData
     * @return array<string, int|string>
     * @since   5.2
     */
    #[\Override]
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
            'packageID' => $this->installation->getPackage()->packageID,
            'providerName' => $element->getAttribute('name'),
        ];

        $showOrder = $element->getElementsByTagName('showorder')->item(0);
        if ($showOrder) {
            $data['showOrder'] = $showOrder->nodeValue;
        } elseif ($saveData) {
            $data['showOrder'] = $this->getShowOrder(null);
        }

        return $data;
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
     * @return string
     * @since   5.2
     */
    protected function getXsdFilename()
    {
        return 'acpSearchProvider';
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

        \assert($this->installation instanceof DevtoolsPackageInstallationDispatcher);
        $dataContainer->appendChildren([
            TextFormField::create('providerName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.acpSearchProvider.providerName')
                ->description(
                    'wcf.acp.pip.acpSearchProvider.providerName.description',
                    ['project' => $this->installation->getProject()]
                )
                ->required()
                ->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
                    'wcf.acp.pip.acpSearchProvider.providerName',
                    4
                ))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                    ) {
                        $providerList = new ACPSearchProviderList();
                        $providerList->getConditionBuilder()->add('providerName = ?', [$formField->getValue()]);

                        if ($providerList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.acpSearchProvider.providerName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            ClassNameFormField::create('className')
                ->objectProperty('classname')
                ->required()
                ->implementedInterface(IACPSearchResultProvider::class),

            IntegerFormField::create('showOrder')
                ->objectProperty('showorder')
                ->label('wcf.form.field.showOrder')
                ->description('wcf.acp.pip.acpSearchProvider.showOrder.description')
                ->nullable()
                ->minimum(1),
        ]);
    }

    /**
     * @return void
     * @since   5.2
     */
    #[\Override]
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'providerName' => 'wcf.acp.pip.acpSearchProvider.providerName',
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

        $acpSearchProvider = $document->createElement($this->tagName);
        $acpSearchProvider->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $acpSearchProvider,
            [
                'classname',
                'showorder' => null,
            ],
            $form
        );

        return $acpSearchProvider;
    }
}
