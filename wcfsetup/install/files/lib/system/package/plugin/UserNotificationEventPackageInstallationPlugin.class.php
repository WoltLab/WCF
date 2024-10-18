<?php

namespace wcf\system\package\plugin;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\event\UserNotificationEventEditor;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes user notification events.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserNotificationEventPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = UserNotificationEventEditor::class;

    /**
     * @inheritDoc
     */
    public $tableName = 'user_notification_event';

    /**
     * @inheritDoc
     */
    public $tagName = 'event';

    /**
     * preset event ids
     * @var int[]
     */
    protected $presetEventIDs = [];

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       packageID = ?
                        AND objectTypeID = ?
                        AND eventName = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $this->installation->getPackageID(),
                $this->getObjectTypeID($item['elements']['objecttype']),
                $item['elements']['name'],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $presetMailNotificationType = 'none';
        if (isset($data['elements']['presetmailnotificationtype']) && ($data['elements']['presetmailnotificationtype'] == 'instant' || $data['elements']['presetmailnotificationtype'] == 'daily')) {
            $presetMailNotificationType = $data['elements']['presetmailnotificationtype'];
        }

        return [
            'eventName' => $data['elements']['name'],
            'className' => $data['elements']['classname'],
            'objectTypeID' => $this->getObjectTypeID($data['elements']['objecttype']),
            'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
            'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
            'preset' => !empty($data['elements']['preset']) ? 1 : 0,
            'presetMailNotificationType' => $presetMailNotificationType,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function import(array $row, array $data)
    {
        /** @var UserNotificationEvent $event */
        $event = parent::import($row, $data);

        if (empty($row) && $data['preset']) {
            $this->presetEventIDs[$event->eventID] = $data['presetMailNotificationType'];
        }

        return $event;
    }

    /**
     * @inheritDoc
     */
    protected function cleanup()
    {
        if (empty($this->presetEventIDs)) {
            return;
        }

        $sql = "INSERT IGNORE INTO  wcf1_user_notification_event_to_user
                                    (userID, eventID, mailNotificationType)
                SELECT              userID, ?, ?
                FROM                wcf1_user";
        $statement = WCF::getDB()->prepare($sql);
        WCF::getDB()->beginTransaction();
        foreach ($this->presetEventIDs as $eventID => $mailNotificationType) {
            $statement->execute([$eventID, $mailNotificationType]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   objectTypeID = ?
                    AND eventName = ?";
        $parameters = [
            $data['objectTypeID'],
            $data['eventName'],
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * Gets the id of given object type id.
     *
     * @param string $objectType
     * @return      int
     * @throws      SystemException
     */
    protected function getObjectTypeID($objectType)
    {
        // get object type id
        $sql = "SELECT  object_type.objectTypeID
                FROM    wcf1_object_type object_type
                WHERE   object_type.objectType = ?
                    AND object_type.definitionID IN (
                            SELECT  definitionID
                            FROM    wcf1_object_type_definition
                            WHERE   definitionName = 'com.woltlab.wcf.notification.objectType'
                        )";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$objectType]);
        $row = $statement->fetchArray();
        if (empty($row['objectTypeID'])) {
            throw new SystemException("unknown notification object type '" . $objectType . "' given");
        }

        return $row['objectTypeID'];
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return ['objectType'];
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
            TextFormField::create('eventName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.userNotificationEvent.eventName')
                ->description('wcf.acp.pip.userNotificationEvent.eventName.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (!\preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.userNotificationEvent.eventName.error.format'
                            )
                        );
                    }
                })),

            SingleSelectionFormField::create('objectType')
                ->objectProperty('objecttype')
                ->label('wcf.acp.pip.userNotificationEvent.objectType')
                ->description('wcf.acp.pip.userNotificationEvent.objectType.description')
                ->required()
                ->options(static function () {
                    $options = [];
                    foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.notification.objectType') as $objectType) {
                        $options[$objectType->objectType] = $objectType->objectType;
                    }

                    \asort($options);

                    return $options;
                })
                // validate the uniqueness of the `name` field after knowing that the selected object type is valid
                ->addValidator(new FormFieldValidator('nameUniqueness', function (SingleSelectionFormField $formField) {
                    /** @var TextFormField $nameField */
                    $nameField = $formField->getDocument()->getNodeById('eventName');

                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getElementsByTagName('name')->item(0)->nodeValue !== $nameField->getSaveValue()
                        || $this->editedEntry->getElementsByTagName('objecttype')->item(0)->nodeValue !== $formField->getSaveValue()
                    ) {
                        $eventList = new UserNotificationEventList();
                        $eventList->getConditionBuilder()->add(
                            'user_notification_event.eventName = ?',
                            [$nameField->getSaveValue()]
                        );
                        $eventList->getConditionBuilder()->add('user_notification_event.objectTypeID = ?', [
                            ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                                'com.woltlab.wcf.notification.objectType',
                                $formField->getSaveValue()
                            ),
                        ]);
                        $eventList->getConditionBuilder()->add(
                            'user_notification_event.objectTypeID = ?',
                            [
                                ObjectTypeCache::getInstance()->getObjectTypeByName(
                                    'com.woltlab.wcf.notification.objectType',
                                    $formField->getSaveValue()
                                )->objectTypeID,
                            ]
                        );

                        if ($eventList->countObjects() > 0) {
                            $nameField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.userNotificationEvent.eventName.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            ClassNameFormField::create()
                ->objectProperty('classname')
                ->required()
                ->implementedInterface(IUserNotificationEvent::class),

            BooleanFormField::create('preset')
                ->label('wcf.acp.pip.userNotificationEvent.preset')
                ->description('wcf.acp.pip.userNotificationEvent.preset.description'),

            OptionFormField::create()
                ->description('wcf.acp.pip.userNotificationEvent.options.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                )),

            UserGroupOptionFormField::create()
                ->description('wcf.acp.pip.userNotificationEvent.permissions.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                )),

            SingleSelectionFormField::create('presetMailNotificationType')
                ->objectProperty('presetmailnotificationtype')
                ->label('wcf.acp.pip.userNotificationEvent.presetMailNotificationType')
                ->description('wcf.acp.pip.userNotificationEvent.presetMailNotificationType.description')
                ->nullable()
                ->options([
                    '' => 'wcf.user.notification.mailNotificationType.none',
                    'daily' => 'wcf.user.notification.mailNotificationType.daily',
                    'instant' => 'wcf.user.notification.mailNotificationType.instant',
                ]),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
            'eventName' => $element->getElementsByTagName('name')->item(0)->nodeValue,
            'packageID' => $this->installation->getPackage()->packageID,
            'preset' => 0,
        ];

        $objectType = $element->getElementsByTagName('objecttype')->item(0)->nodeValue;
        if ($saveData) {
            $data['objectTypeID'] = $this->getObjectTypeID($objectType);
        } else {
            $data['objectType'] = $objectType;
        }

        $options = $element->getElementsByTagName('options')->item(0);
        if ($options) {
            $data['options'] = StringUtil::normalizeCsv($options->nodeValue);
        } elseif ($saveData) {
            $data['options'] = '';
        }

        $permissions = $element->getElementsByTagName('permissions')->item(0);
        if ($permissions) {
            $data['permissions'] = StringUtil::normalizeCsv($permissions->nodeValue);
        } elseif ($saveData) {
            $data['permissions'] = '';
        }

        // the presence of a `preset` element is treated as `<preset>1</preset>
        if ($element->getElementsByTagName('preset')->length === 1) {
            $data['preset'] = 1;
        } elseif ($saveData) {
            $data['preset'] = 0;
        }

        $presetMailNotificationType = $element->getElementsByTagName('presetmailnotificationtype')->item(0);
        if ($presetMailNotificationType && \in_array($presetMailNotificationType->nodeValue, ['instant', 'daily'])) {
            $data['presetMailNotificationType'] = $presetMailNotificationType->nodeValue;
        } elseif ($saveData) {
            $data['presetMailNotificationType'] = 'none';
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
            $element->getElementsByTagName('name')->item(0)->nodeValue . '/'
            . $element->getElementsByTagName('objecttype')->item(0)->nodeValue
        );
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'eventName' => 'wcf.acp.pip.userNotificationEvent.eventName',
            'className' => 'wcf.form.field.className',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $event = $document->createElement($this->tagName);

        $this->appendElementChildren(
            $event,
            [
                'name',
                'objecttype',
                'classname',
                'options' => '',
                'permissions' => '',
                'preset' => 0,
                'presetmailnotificationtype' => '',
            ],
            $form
        );

        return $event;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $userNotificationEvent = $element->ownerDocument->createElement($this->tagName);

        foreach (['name', 'objecttype'] as $childElement) {
            $userNotificationEvent->appendChild($element->ownerDocument->createElement(
                $childElement,
                $element->getElementsByTagName($childElement)->item(0)->nodeValue
            ));
        }

        return $userNotificationEvent;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $elements = [];
        foreach (['name', 'objecttype'] as $childElement) {
            $elements[$childElement] = $element->getElementsByTagName($childElement)->item(0)->nodeValue;
        }

        $this->handleDelete([['elements' => $elements]]);
    }
}
