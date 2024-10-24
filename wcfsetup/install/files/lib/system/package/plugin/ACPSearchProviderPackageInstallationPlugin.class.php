<?php

namespace wcf\system\package\plugin;

use wcf\data\acp\search\provider\ACPSearchProviderEditor;
use wcf\data\acp\search\provider\ACPSearchProviderList;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    protected function cleanup()
    {
        ACPSearchProviderCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['providerName'];
    }

    /**
     * @see \wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return 'acpSearchProvider.xml';
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
        return 'acpSearchProvider';
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
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'providerName' => 'wcf.acp.pip.acpSearchProvider.providerName',
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
