<?php

namespace wcf\system\package\plugin;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\clipboard\action\ClipboardActionEditor;
use wcf\data\clipboard\action\ClipboardActionList;
use wcf\system\clipboard\action\IClipboardAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Installs, updates and deletes clipboard actions.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ClipboardActionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = ClipboardActionEditor::class;

    /**
     * list of pages per action id
     * @var mixed[][]
     */
    protected $pages = [];

    /**
     * @inheritDoc
     */
    public $tagName = 'action';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       actionName = ?
                        AND actionClassName = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $item['attributes']['name'],
                $item['elements']['actionclassname'],
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
        if ($element->tagName == 'pages') {
            $nodeValue = [];

            $pages = $xpath->query('child::ns:page', $element);
            foreach ($pages as $page) {
                $nodeValue[] = $page->nodeValue;
            }
        }

        $elements[$element->tagName] = $nodeValue;
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $showOrder = isset($data['elements']['showorder']) ? \intval($data['elements']['showorder']) : null;
        $showOrder = $this->getShowOrder($showOrder, $data['elements']['actionclassname'], 'actionClassName');

        return [
            'actionClassName' => $data['elements']['actionclassname'],
            'actionName' => $data['attributes']['name'],
            'pages' => $data['elements']['pages'],
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
                WHERE   actionName = ?
                    AND actionClassName = ?
                    AND packageID = ?";
        $parameters = [
            $data['actionName'],
            $data['actionClassName'],
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
    protected function import(array $row, array $data)
    {
        // extract pages
        $pages = $data['pages'];
        unset($data['pages']);

        /** @var ClipboardAction $action */
        $action = parent::import($row, $data);

        // store pages for later import
        $this->pages[$action->actionID] = $pages;

        return $action;
    }

    /**
     * @inheritDoc
     */
    protected function postImport()
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('packageID = ?', [$this->installation->getPackageID()]);
        $conditionBuilder->add('actionID IN (?)', [\array_keys($this->pages)]);

        // clear pages
        $sql = "DELETE FROM wcf1_clipboard_page
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        if (!empty($this->pages)) {
            // insert pages
            $sql = "INSERT INTO wcf1_clipboard_page
                                (pageClassName, packageID, actionID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($this->pages as $actionID => $pages) {
                foreach ($pages as $pageClassName) {
                    $statement->execute([
                        $pageClassName,
                        $this->installation->getPackageID(),
                        $actionID,
                    ]);
                }
            }
        }
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
            TextFormField::create('actionName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.clipboardAction.actionName')
                ->description('wcf.acp.pip.clipboardAction.actionName.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (!\preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.clipboardAction.actionName.error.format'
                            )
                        );
                    }
                })),

            ClassNameFormField::create('actionClassName')
                ->label('wcf.acp.pip.clipboardAction.actionClassName')
                ->objectProperty('actionclassname')
                ->required()
                ->implementedInterface(IClipboardAction::class)
                ->addValidator(new FormFieldValidator('uniqueness', function (ClassNameFormField $formField) {
                    /** @var TextFormField $actionNameFormField */
                    $actionNameFormField = $formField->getDocument()->getNodeById('actionName');

                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || (
                            $this->editedEntry->getAttribute('name') !== $actionNameFormField->getSaveValue()
                            || $this->editedEntry->getElementsByTagName('actionclassname')->item(0)->nodeValue !== $formField->getSaveValue()
                        )
                    ) {
                        $clipboardActionList = new ClipboardActionList();
                        $clipboardActionList->getConditionBuilder()->add(
                            'actionName = ?',
                            [$actionNameFormField->getSaveValue()]
                        );
                        $clipboardActionList->getConditionBuilder()->add(
                            'actionClassName = ?',
                            [$formField->getSaveValue()]
                        );

                        if ($clipboardActionList->countObjects() > 0) {
                            $actionNameFormField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.clipboardAction.actionClassName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            IntegerFormField::create('showOrder')
                ->objectProperty('showorder')
                ->label('wcf.form.field.showOrder')
                ->description('wcf.acp.pip.clipboardAction.showOrder.description')
                ->nullable(),

            ItemListFormField::create('pages')
                ->label('wcf.acp.pip.clipboardAction.pages')
                ->description('wcf.acp.pip.clipboardAction.pages.description')
                ->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_ARRAY)
                ->required(),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'actionClassName' => $element->getElementsByTagName('actionclassname')->item(0)->nodeValue,
            'actionName' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
            'pages' => [],
        ];

        $showOrder = $element->getElementsByTagName('showorder')->item(0);
        if ($showOrder !== null) {
            $data['showOrder'] = $showOrder->nodeValue;
        }
        if ($saveData && $this->editedEntry === null) {
            // only set explicit showOrder when adding new clipboard actions
            $data['showOrder'] = $this->getShowOrder(
                $data['showOrder'] ?? null,
                $data['actionClassName'],
                'actionClassName'
            );
        }

        /** @var \DOMElement $page */
        foreach ($element->getElementsByTagName('pages')->item(0)->childNodes as $page) {
            if ($page->nodeName === 'page') {
                $data['pages'][] = $page->nodeValue;
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
        return \sha1(
            $element->getElementsByTagName('actionclassname')->item(0)->nodeValue . '/'
            . $element->getAttribute('name')
        );
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'actionName' => 'wcf.acp.pip.clipboardAction.actionName',
            'actionClassName' => 'wcf.acp.pip.clipboardAction.actionClassName',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $formData = $form->getData();
        $data = $formData['data'];

        $clipboardAction = $document->createElement($this->tagName);
        $clipboardAction->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $clipboardAction,
            [
                'actionclassname',
                'showorder' => null,
            ],
            $form
        );

        $pages = $document->createElement('pages');
        $clipboardAction->appendChild($pages);

        foreach ($formData['pages'] as $page) {
            $pages->appendChild($document->createElement('page', $page));
        }

        return $clipboardAction;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $clipboardAction = $element->ownerDocument->createElement($this->tagName);
        $clipboardAction->setAttribute('name', $element->getAttribute('name'));

        $clipboardAction->appendChild($element->ownerDocument->createElement(
            'actionclassname',
            $element->getElementsByTagName('actionclassname')->item(0)->nodeValue
        ));

        return $clipboardAction;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $actionClassName = $element->getElementsByTagName('actionclassname')->item(0)->nodeValue;

        $this->handleDelete([
            [
                'attributes' => ['name' => $element->getAttribute('name')],
                'elements' => ['actionclassname' => $actionClassName],
            ],
        ]);
    }
}
