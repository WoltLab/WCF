<?php

namespace wcf\system\package\plugin;

use wcf\data\event\listener\EventListenerEditor;
use wcf\data\event\listener\EventListenerList;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\event\EventHandler;
use wcf\system\event\IEvent;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes event listeners.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin {
        setEntryData as private traitSetEntryData;
        editEntry as private traitEditEntry;
    }

    /**
     * @inheritDoc
     */
    public $className = EventListenerEditor::class;

    /**
     * @inheritDoc
     */
    public $tagName = 'eventlistener';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       packageID = ?
                        AND environment = ?
                        AND eventClassName = ?
                        AND eventName = ?
                        AND inherit = ?
                        AND listenerClassName = ?";
        $legacyStatement = WCF::getDB()->prepare($sql);

        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       packageID = ?
                        AND listenerName = ?";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($items as $item) {
            if (!isset($item['attributes']['name'])) {
                $legacyStatement->execute([
                    $this->installation->getPackageID(),
                    $item['elements']['environment'] ?? 'user',
                    $item['elements']['eventclassname'],
                    $item['elements']['eventname'],
                    $item['elements']['inherit'] ?? 0,
                    $item['elements']['listenerclassname'],
                ]);
            } else {
                $statement->execute([
                    $this->installation->getPackageID(),
                    $item['attributes']['name'],
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $nice = isset($data['elements']['nice']) ? \intval($data['elements']['nice']) : 0;
        if ($nice < -128) {
            $nice = -128;
        } elseif ($nice > 127) {
            $nice = 127;
        }

        $eventName = EventHandler::DEFAULT_EVENT_NAME;
        if (!empty($data['elements']['eventname'])) {
            $eventName = StringUtil::normalizeCsv($data['elements']['eventname']);
        }

        return [
            'environment' => $data['elements']['environment'] ?? 'user',
            'eventClassName' => $data['elements']['eventclassname'],
            'eventName' => $eventName,
            'inherit' => isset($data['elements']['inherit']) ? \intval($data['elements']['inherit']) : 0,
            'listenerClassName' => $data['elements']['listenerclassname'],
            'listenerName' => $data['attributes']['name'],
            'niceValue' => $nice,
            'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
            'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   packageID = ?
                    AND listenerName = ?";
        $parameters = [
            $this->installation->getPackageID(),
            $data['listenerName'],
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        parent::uninstall();

        // clear cache immediately
        EventListenerCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['listenerName'];
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
            TextFormField::create('listenerName')
                ->label('wcf.acp.pip.eventListener.listenerName')
                ->description('wcf.acp.pip.eventListener.listenerName.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (\preg_match('~^[a-z][A-z0-9]*$~', $formField->getSaveValue()) !== 1) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.eventListener.listenerName.error.format'
                            )
                        );
                    }
                }))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                    ) {
                        $eventListenerList = new EventListenerList();
                        $eventListenerList->getConditionBuilder()->add('listenerName = ?', [$formField->getValue()]);
                        $eventListenerList->getConditionBuilder()->add(
                            'packageID = ?',
                            [$this->installation->getPackageID()]
                        );

                        if ($eventListenerList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.eventListener.listenerName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            ClassNameFormField::create('eventClassName')
                ->objectProperty('eventclassname')
                ->label('wcf.acp.pip.eventListener.eventClassName')
                ->description('wcf.acp.pip.eventListener.eventClassName.description')
                ->required()
                ->instantiable(false),

            ItemListFormField::create('eventName')
                ->objectProperty('eventname')
                ->label('wcf.acp.pip.eventListener.eventName')
                ->description('wcf.acp.pip.eventListener.eventName.description'),

            ClassNameFormField::create('listenerClassName')
                ->objectProperty('listenerclassname')
                ->label('wcf.acp.pip.eventListener.listenerClassName')
                ->required()
                ->addValidator(new FormFieldValidator('callable', static function (ClassNameFormField $formField) {
                    $listenerClassName = $formField->getValue();
                    /** @var TextFormField $eventClassNameField */
                    $eventClassNameField = $formField->getDocument()->getNodeById('eventClassName');
                    $eventClassName = $eventClassNameField->getValue();

                    if (\is_subclass_of($eventClassName, IEvent::class)) {
                        if (!\is_callable(new $listenerClassName())) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'noCallable',
                                    'wcf.acp.pip.eventListener.listenerClassName.error.noCallable',
                                    [
                                        'listenerClassName' => $listenerClassName,
                                    ]
                                )
                            );
                        }
                    } elseif (!\is_subclass_of($listenerClassName, IParameterizedEventListener::class)) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'interface',
                                'wcf.form.field.className.error.interface',
                                [
                                    'interface' => IParameterizedEventListener::class,
                                ]
                            )
                        );
                    }
                })),

            SingleSelectionFormField::create('environment')
                ->label('wcf.acp.pip.eventListener.environment')
                ->description('wcf.acp.pip.eventListener.environment.description')
                ->options([
                    'admin' => 'admin',
                    'user' => 'user',
                    'all' => 'all',
                ])
                ->value('user'),

            BooleanFormField::create('inherit')
                ->label('wcf.acp.pip.eventListener.inherit')
                ->description('wcf.acp.pip.eventListener.inherit.description'),

            IntegerFormField::create('niceValue')
                ->objectProperty('nice')
                ->label('wcf.acp.pip.eventListener.niceValue')
                ->description('wcf.acp.pip.eventListener.niceValue.description')
                ->nullable()
                ->minimum(-128)
                ->maximum(127),

            OptionFormField::create()
                ->description('wcf.acp.pip.eventListener.options.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ))
                ->available($form->getFormMode() !== IFormDocument::FORM_MODE_CREATE),

            UserGroupOptionFormField::create()
                ->description('wcf.acp.pip.eventListener.permissions.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ))
                ->available($form->getFormMode() !== IFormDocument::FORM_MODE_CREATE),
        ]);
    }

    /**
     * Shows options and permissions if already specified.
     */
    public function setEntryData($identifier, IFormDocument $document): bool
    {
        $options = $document->getNodeById('options');
        \assert($options instanceof OptionFormField);
        $permissions = $document->getNodeById('permissions');
        \assert($permissions instanceof UserGroupOptionFormField);

        $result = $this->traitSetEntryData($identifier, $document);

        if ($result) {
            if (!$options->getValue()) {
                $options->available(false);
            }
            if (!$permissions->getValue()) {
                $permissions->available(false);
            }
        }

        return $result;
    }

    /**
     * Shows options and permissions if already specified.
     */
    public function editEntry(IFormDocument $form, $identifier)
    {
        $options = $form->getNodeById('options');
        \assert($options instanceof OptionFormField);
        $permissions = $form->getNodeById('permissions');
        \assert($permissions instanceof UserGroupOptionFormField);

        $result = $this->traitEditEntry($form, $identifier);

        if (!$options->getValue()) {
            $options->available(false);
        }
        if (!$permissions->getValue()) {
            $permissions->available(false);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $eventName = EventHandler::DEFAULT_EVENT_NAME;
        $eventNameElements = $element->getElementsByTagName('eventname');
        if ($eventNameElements->length) {
            $eventName = StringUtil::normalizeCsv($eventNameElements->item(0)->nodeValue);
        }

        $data = [
            'eventClassName' => $element->getElementsByTagName('eventclassname')->item(0)->nodeValue,
            'eventName' => $eventName,
            'listenerClassName' => $element->getElementsByTagName('listenerclassname')->item(0)->nodeValue,
            'listenerName' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
        ];

        foreach (['environment', 'inherit', 'options', 'permissions'] as $optionalElementProperty) {
            $optionalElement = $element->getElementsByTagName($optionalElementProperty)->item(0);
            if ($optionalElement !== null) {
                $data[$optionalElementProperty] = $optionalElement->nodeValue;
            } elseif ($saveData) {
                switch ($optionalElementProperty) {
                    case 'environment':
                        $data[$optionalElementProperty] = 'user';
                        break;

                    case 'inherit':
                        $data[$optionalElementProperty] = 0;
                        break;

                    case 'options':
                    case 'permissions':
                        $data[$optionalElementProperty] = '';
                        break;
                }
            }
        }

        $niceValue = $element->getElementsByTagName('nice')->item(0);
        if ($niceValue !== null) {
            $data['niceValue'] = $niceValue->nodeValue;
        } elseif ($saveData) {
            $data['niceValue'] = 0;
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
            'listenerName' => 'wcf.acp.pip.eventListener.listenerName',
            'eventClassName' => 'wcf.acp.pip.eventListener.eventClassName',
            'eventName' => 'wcf.acp.pip.eventListener.eventName',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $eventListener = $document->createElement($this->tagName);
        $eventListener->setAttribute('name', $data['listenerName']);

        $this->appendElementChildren(
            $eventListener,
            [
                'eventclassname',
                'eventname' => '',
                'listenerclassname',
                'environment' => 'user',
                'inherit' => 0,
                'nice' => null,
                'options' => '',
                'permissions' => '',
            ],
            $form
        );

        return $eventListener;
    }
}
