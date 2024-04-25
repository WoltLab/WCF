<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\button\wysiwyg\WysiwygPreviewFormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygAttachmentFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\TWysiwygFormNode;

/**
 * Represents the whole container with a WYSIWYG editor and the associated tab menu below it with
 * support for smilies, attchments, settings, and polls.
 *
 * Instead of having to manually set up each individual component, this form container allows to
 * simply create an instance of this class, set some required data for some components, and the
 * setup is complete.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WysiwygFormContainer extends FormContainer
{
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TWysiwygFormNode;

    /**
     * attachment form field
     * @var WysiwygAttachmentFormField
     */
    protected $attachmentField;

    /**
     * attachment-related data used to create an `AttachmentHandler` object for the attachment
     * form field
     * @var null|array
     */
    protected $attachmentData;

    /**
     * `true` if the preview button should be shown and `false` otherwise
     * @var         bool
     * @since       5.3
     */
    protected $enablePreviewButton = true;

    /**
     * name of the relevant message object type
     * @var string
     */
    protected $messageObjectType;

    /**
     * id of the edited object
     * @var int
     */
    protected $objectId;

    /**
     * pre-select attribute of the tab menu
     * @var string
     */
    protected $preselect = 'true';

    /**
     * name of the relevant poll object type
     * @var string
     */
    protected $pollObjectType;

    /**
     * poll form container
     * @var WysiwygPollFormContainer
     */
    protected $pollContainer;

    /**
     * quote-related data used to create the JavaScript quote manager
     * @var null|array
     */
    protected $quoteData;

    /**
     * `true` if the wysiwyg field has to be filled out and `false` otherwise
     * @var bool
     */
    protected $required = false;

    /**
     * settings form container
     * @var FormContainer
     */
    protected $settingsContainer;

    /**
     * setting nodes that will be added to the settings container when it is created
     * @var IFormChildNode[]
     */
    protected $settingsNodes = [];

    /**
     * form container for smiley categories
     * @var WysiwygSmileyFormContainer
     */
    protected $smiliesContainer;

    /**
     * is `true` if the wysiwyg form field will support mentions, otherwise `false`
     * @var bool
     */
    protected $supportMentions = false;

    /**
     * is `true` if quotes are supported for this container, otherwise `false`
     * @var bool
     */
    protected $supportQuotes = false;

    /**
     * is `true` if smilies are supported for this container, otherwise `false`
     * @var bool
     */
    protected $supportSmilies = true;

    /**
     * actual wysiwyg form field
     * @var WysiwygFormField
     */
    protected $wysiwygField;

    /**
     * @inheritDoc
     * @return  static
     */
    public static function create($id)
    {
        // the actual id is used for the form field containing the text
        return parent::create($id . 'Container');
    }

    /**
     * Adds a node that will be appended to the settings form container when it is built and
     * returns this container.
     *
     * @param IFormChildNode $settingsNode added settings node
     * @return  WysiwygFormContainer        this form field container
     */
    public function addSettingsNode(IFormChildNode $settingsNode)
    {
        if ($this->settingsContainer !== null) {
            // if settings container has already been created, add it directly
            $this->settingsContainer->appendChild($settingsNode);
        } else {
            $this->settingsNodes[] = $settingsNode;
        }

        return $this;
    }

    /**
     * Adds nodes that will be appended to the settings form container when it is built and
     * returns this container.
     *
     * @param IFormChildNode[] $settingsNodes added settings nodes
     * @return  WysiwygFormContainer            this form field container
     */
    public function addSettingsNodes(array $settingsNodes)
    {
        foreach ($settingsNodes as $settingsNode) {
            $this->addSettingsNode($settingsNode);
        }

        return $this;
    }

    /**
     * Sets the attachment-related data used to create an `AttachmentHandler` object for the
     * attachment form field. If no attachment data is set, attachments are not supported.
     *
     * By default, no attachment data is set.
     *
     * @param null|string $objectType name of attachment object type or `null` to unset previous attachment data
     * @param int $parentObjectID id of the parent of the object the attachments belong to or `0` if no such parent exists
     * @return  WysiwygFormContainer            this form container
     * @throws  \BadMethodCallException         if the attachment form field has already been initialized
     */
    public function attachmentData($objectType = null, $parentObjectID = 0)
    {
        if ($this->attachmentField !== null) {
            throw new \BadMethodCallException("The attachment form field '{$this->getId()}' has already been initialized. Use the atatchment form field directly to manipulate attachment data.");
        }

        if ($objectType === null) {
            $this->attachmentData = null;
        } else {
            if (
                ObjectTypeCache::getInstance()->getObjectTypeByName(
                    'com.woltlab.wcf.attachment.objectType',
                    $objectType
                ) === null
            ) {
                throw new \InvalidArgumentException("Unknown attachment object type '{$objectType}' for container '{$this->getId()}'.");
            }

            $this->attachmentData = [
                'objectType' => $objectType,
                'parentObjectID' => $parentObjectID,
            ];
        }

        return $this;
    }

    /**
     * Sets whether the preview button should be shown or not and returns this form container.
     *
     * By default, the preview button is shown.
     *
     * @param bool $enablePreviewButton
     * @return      WysiwygFormContainer            this form container
     * @throws      \BadMethodCallException         if the form field container has already been populated yet
     * @since       5.3
     */
    public function enablePreviewButton($enablePreviewButton = true)
    {
        if ($this->isPopulated) {
            throw new \BadMethodCallException(
                "Enabling and disabling the preview button is only possible before the form has been built for container '{$this->getId()}'."
            );
        }

        $this->enablePreviewButton = $enablePreviewButton;

        return $this;
    }

    /**
     * Returns the form field handling attachments.
     *
     * @return  WysiwygAttachmentFormField
     * @throws  \BadMethodCallException     if the form field container has not been populated yet/form has not been built yet
     */
    public function getAttachmentField()
    {
        if ($this->attachmentField === null) {
            throw new \BadMethodCallException(
                "Wysiwyg form field can only be requested after the form has been built for container '{$this->getId()}'."
            );
        }

        return $this->attachmentField;
    }

    /**
     * Returns the id of the edited object or `0` if no object is edited.
     *
     * @return  int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Returns the value of the wysiwyg tab menu's `data-preselect` attribute used to determine
     * which tab is preselected.
     *
     * By default, `'true'` is returned which is used to pre-select the first tab.
     *
     * @return  string
     */
    public function getPreselect()
    {
        return $this->preselect;
    }

    /**
     * Returns the wysiwyg form container with all poll-related fields.
     *
     * @return  WysiwygPollFormContainer
     * @throws  \BadMethodCallException     if the form field container has not been populated yet/form has not been built yet
     */
    public function getPollContainer()
    {
        if ($this->pollContainer === null) {
            throw new \BadMethodCallException(
                "Wysiwyg form field can only be requested after the form has been built for container '{$this->getId()}'."
            );
        }

        return $this->pollContainer;
    }

    /**
     * Returns the form container for all settings-related fields.
     *
     * @return  FormContainer
     * @throws  \BadMethodCallException     if the form field container has not been populated yet/form has not been built yet
     */
    public function getSettingsContainer()
    {
        if ($this->settingsContainer === null) {
            throw new \BadMethodCallException(
                "Wysiwyg form field can only be requested after the form has been built for container '{$this->getId()}'."
            );
        }

        return $this->settingsContainer;
    }

    /**
     * Returns the form container for smiley categories.
     *
     * @return  WysiwygSmileyFormContainer
     * @throws  \BadMethodCallException     if the form field container has not been populated yet/form has not been built yet
     */
    public function getSmiliesContainer()
    {
        if ($this->smiliesContainer === null) {
            throw new \BadMethodCallException(
                "Smilies form field container can only be requested after the form has been built for container '{$this->getId()}'."
            );
        }

        return $this->smiliesContainer;
    }

    /**
     * Returns the wysiwyg form field handling the actual text.
     *
     * @return  WysiwygFormField
     * @throws  \BadMethodCallException     if the form field container has not been populated yet/form has not been built yet
     */
    public function getWysiwygField()
    {
        if ($this->wysiwygField === null) {
            throw new \BadMethodCallException(
                "Wysiwyg form field can only be requested after the form has been built for container '{$this->getId()}'."
            );
        }

        return $this->wysiwygField;
    }

    /**
     * @inheritDoc
     */
    public function id($id)
    {
        $this->wysiwygId(\substr($id, 0, -\strlen('Container')));

        return parent::id($id);
    }

    /**
     * Returns `true` if the wysiwyg field has to be filled out and returns `false` otherwise.
     * By default, the wysiwyg field does not have to be filled out.
     *
     * @return  bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Returns `true` if the preview button will be shown and returns `false` otherwise.
     *
     * By default, the preview button is shown.
     *
     * @return      bool
     * @since       5.3
     */
    public function isPreviewButtonEnabled()
    {
        return $this->enablePreviewButton;
    }

    /**
     * @since   5.3
     * @inheritDoc
     */
    public function markAsRequired()
    {
        return $this->getWysiwygField()->isRequired();
    }

    /**
     * Sets the message object type used by the wysiwyg form field.
     *
     * @param string $messageObjectType message object type for wysiwyg form field
     * @return  WysiwygFormContainer            this container
     * @throws  \InvalidArgumentException       if the given string is no message object type
     */
    public function messageObjectType($messageObjectType)
    {
        if (
            ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.message',
                $messageObjectType
            ) === null
        ) {
            throw new \InvalidArgumentException(
                "Unknown message object type '{$messageObjectType}' for container '{$this->getId()}'."
            );
        }

        if ($this->wysiwygField !== null) {
            $this->wysiwygField->objectType($messageObjectType);
        } else {
            $this->messageObjectType = $messageObjectType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        $this->objectId = $object->{$object::getDatabaseTableIndexName()};

        $this->setAttachmentHandler();

        return parent::updatedObject($data, $object);
    }

    /**
     * Sets the poll object type used by the poll form field container.
     *
     * By default, no poll object type is set, thus the poll form field container is not available.
     *
     * @param string $pollObjectType poll object type for wysiwyg form field
     * @return  WysiwygFormContainer            this container
     * @throws  \InvalidArgumentException       if the given string is no poll object type
     */
    public function pollObjectType($pollObjectType)
    {
        if (ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.poll', $pollObjectType) === null) {
            throw new \InvalidArgumentException(
                "Unknown poll object type '{$pollObjectType}' for container '{$this->getId()}'."
            );
        }

        if ($this->pollContainer !== null) {
            $this->pollContainer->objectType($pollObjectType);
        } else {
            $this->pollObjectType = $pollObjectType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->wysiwygField = WysiwygFormField::create($this->wysiwygId)
            ->objectType($this->messageObjectType)
            ->minimumLength($this->getMinimumLength())
            ->maximumLength($this->getMaximumLength())
            ->required($this->isRequired())
            ->supportMentions($this->supportMentions)
            ->supportQuotes($this->supportQuotes);
        if ($this->quoteData !== null) {
            $this->wysiwygField->quoteData(
                $this->quoteData['objectType'],
                $this->quoteData['actionClass'],
                $this->quoteData['selectors']
            );
        }
        $this->smiliesContainer = WysiwygSmileyFormContainer::create($this->wysiwygId . 'SmiliesTab')
            ->wysiwygId($this->getWysiwygId())
            ->label('wcf.message.smilies')
            ->available($this->supportSmilies);
        $this->attachmentField = WysiwygAttachmentFormField::create($this->wysiwygId . 'Attachments')
            ->wysiwygId($this->getWysiwygId());
        $this->settingsContainer = FormContainer::create($this->wysiwygId . 'SettingsContainer')
            ->appendChildren($this->settingsNodes);
        $this->pollContainer = WysiwygPollFormContainer::create($this->wysiwygId . 'PollContainer')
            ->wysiwygId($this->getWysiwygId());
        if ($this->pollObjectType) {
            $this->pollContainer->objectType($this->pollObjectType);
        }

        $this->appendChildren([
            $this->wysiwygField,
            WysiwygTabMenuFormContainer::create($this->wysiwygId . 'Tabs')
                ->attribute('data-preselect', $this->getPreselect())
                ->attribute('data-wysiwyg-container-id', $this->wysiwygId)
                ->useAnchors(false)
                ->appendChildren([
                    $this->smiliesContainer,

                    TabFormContainer::create($this->wysiwygId . 'AttachmentsTab')
                        ->addClass('formAttachmentContent')
                        ->label('wcf.attachment.attachments')
                        ->appendChild(
                            FormContainer::create($this->wysiwygId . 'AttachmentsContainer')
                                ->appendChild($this->attachmentField)
                        ),

                    TabFormContainer::create($this->wysiwygId . 'SettingsTab')
                        ->label('wcf.message.settings')
                        ->appendChild($this->settingsContainer),

                    TabFormContainer::create($this->wysiwygId . 'PollTab')
                        ->label('wcf.poll.management')
                        ->appendChild($this->pollContainer),
                ]),
        ]);

        if ($this->attachmentData !== null) {
            $this->setAttachmentHandler();
        }
        $this->wysiwygField->supportAttachments($this->attachmentField->isAvailable());

        if ($this->enablePreviewButton) {
            $this->getDocument()->addButton(
                WysiwygPreviewFormButton::create($this->getWysiwygId() . 'PreviewButton')
                    ->objectType($this->messageObjectType)
                    ->wysiwygId($this->getWysiwygId())
                    ->objectId($this->getObjectId())
            );
        }

        EventHandler::getInstance()->fireAction($this, 'populate');
    }

    /**
     * Sets the value of the wysiwyg tab menu's `data-preselect` attribute used to determine which
     * tab is preselected.
     *
     * @param string $preselect id of preselected tab, `'true'` for first tab, or non-existing id for no preselected tab
     * @return  WysiwygFormContainer
     */
    public function preselect($preselect = 'true')
    {
        $this->preselect = $preselect;

        return $this;
    }

    /**
     * Sets the data required for advanced quote support for when quotable content is present
     * on the active page and returns this container.
     *
     * Calling this method automatically enables quote support for this container.
     *
     * @param string $objectType name of the relevant `com.woltlab.wcf.message.quote` object type
     * @param string $actionClass action class implementing `wcf\data\IMessageQuoteAction`
     * @param string[] $selectors selectors for the quotable content (required keys: `container`, `messageBody`, and `messageContent`)
     * @return  static
     */
    public function quoteData($objectType, $actionClass, array $selectors = [])
    {
        if ($this->wysiwygField !== null) {
            $this->wysiwygField->quoteData($objectType, $actionClass, $selectors);
        } else {
            $this->supportQuotes();

            // the parameters are validated by `WysiwygFormField`
            $this->quoteData = [
                'actionClass' => $actionClass,
                'objectType' => $objectType,
                'selectors' => $selectors,
            ];
        }

        return $this;
    }

    /**
     * Sets whether it is required to fill out the wysiwyg field and returns this container.
     *
     * @param bool $required determines if field has to be filled out
     * @return  static              this container
     */
    public function required($required = true)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Sets the attachment handler of the attachment form field.
     */
    protected function setAttachmentHandler()
    {
        if ($this->attachmentData !== null) {
            $this->attachmentField->attachmentHandler(
                new AttachmentHandler(
                    $this->attachmentData['objectType'],
                    $this->getObjectId(),
                    '.',
                    $this->attachmentData['parentObjectID']
                )
            );
        }
    }

    /**
     * Sets if mentions are supported by the editor field and returns this form container.
     *
     * By default, mentions are not supported.
     *
     * @param bool $supportMentions
     * @return  WysiwygFormContainer        this form container
     */
    public function supportMentions($supportMentions = true)
    {
        if ($this->wysiwygField !== null) {
            $this->wysiwygField->supportMentions($supportMentions);
        } else {
            $this->supportMentions = $supportMentions;
        }

        return $this;
    }

    /**
     * Sets if quotes are supported by the editor field and returns this form container.
     *
     * By default, quotes are not supported.
     *
     * @param bool $supportQuotes
     * @return  WysiwygFormContainer        this form container
     */
    public function supportQuotes($supportQuotes = true)
    {
        if ($this->wysiwygField !== null) {
            $this->wysiwygField->supportQuotes($supportQuotes);
        } else {
            $this->supportQuotes = $supportQuotes;
        }

        return $this;
    }

    /**
     * Sets if smilies are supported for this form container and returns this form container.
     *
     * By default, smilies are supported.
     *
     * @param bool $supportSmilies
     * @return  WysiwygFormContainer        this form container
     */
    public function supportSmilies($supportSmilies = true)
    {
        if (!\MODULE_SMILEY) {
            $supportSmilies = false;
        }

        if ($this->smiliesContainer !== null) {
            $this->smiliesContainer->available($supportSmilies);
        } else {
            $this->supportSmilies = $supportSmilies;
        }

        return $this;
    }
}
