<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\button\wysiwyg\WysiwygPreviewFormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IBuilderNode;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygAttachmentFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\system\style\FontAwesomeIcon;

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
class WysiwygFormContainer extends FormContainer implements IBuilderNode
{
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TWysiwygFormNode;

    /**
     * attachment form field
     * @var ?WysiwygAttachmentFormField
     */
    protected $attachmentField;

    /**
     * attachment-related data used to create an `AttachmentHandler` object for the attachment
     * form field
     * @var ?array{
     *  objectType: string,
     *  parentObjectID: int,
     *  objectID: int,
     * }
     */
    protected $attachmentData;

    /**
     * identifier used to autosave the wysiwyg field value; if empty, autosave is disabled
     * @since 6.2
     */
    protected string $autosaveId = '';

    /**
     * `true` if the preview button should be shown and `false` otherwise
     * @var         bool
     * @since       5.3
     */
    protected $enablePreviewButton = true;

    /**
     * last time the wysiwyg field has been edited
     * @since 6.2
     */
    protected int $lastEditTime = 0;

    /**
     * name of the relevant message object type
     * @var ?string
     */
    protected $messageObjectType;

    /**
     * id of the edited object
     * @var ?int
     */
    protected $objectId;

    /**
     * pre-select attribute of the tab menu
     * @var string
     */
    protected $preselect = 'true';

    /**
     * name of the relevant poll object type
     * @var ?string
     */
    protected $pollObjectType;

    /**
     * poll form container
     * @var ?WysiwygPollFormContainer
     */
    protected $pollContainer;

    /**
     * `true` if the wysiwyg field has to be filled out and `false` otherwise
     * @var bool
     */
    protected $required = false;

    /**
     * settings form container
     * @var ?FormContainer
     */
    protected $settingsContainer;

    /**
     * setting nodes that will be added to the settings container when it is created
     * @var IFormChildNode[]
     */
    protected $settingsNodes = [];

    /**
     * form container for smiley categories
     * @var ?WysiwygSmileyFormContainer
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
    protected $supportSmilies = \MODULE_SMILEY !== 0;

    /**
     * actual wysiwyg form field
     * @var ?WysiwygFormField
     */
    protected $wysiwygField;

    protected WysiwygQuoteFormContainer $quoteContainer;

    /**
     * callback transferring this field's save value into a `DatabaseObjectBuilder`
     * @var ?\Closure(\wcf\data\DatabaseObjectBuilder<*>, static): void
     * @since 6.3
     */
    protected ?\Closure $saveValueCallback = null;

    /**
     * callback loading this field's value from an `IStorableObject`
     * @var ?\Closure(\wcf\data\IStorableObject, static): void
     * @since 6.3
     */
    protected ?\Closure $loadValueCallback = null;

    /**
     * @return  static
     */
    #[\Override]
    public static function create(string $id)
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
     * @param ?int $objectID id of the object the attachments belong to
     * @throws  \BadMethodCallException         if the attachment form field has already been initialized
     */
    public function attachmentData(?string $objectType = null, int $parentObjectID = 0, ?int $objectID = null): static
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
                'objectID' => $objectID,
            ];
        }

        return $this;
    }

    /**
     * Sets the identifier used to autosave the wysiwyg field value and returns this form container.
     * If an empty string is given, autosave is disabled.
     *
     * @since 6.2
     */
    public function autosaveId(string $autosaveId): static
    {
        $this->autosaveId = $autosaveId;

        if ($this->wysiwygField !== null) {
            $this->wysiwygField->autosaveId($autosaveId);
        }

        return $this;
    }

    /**
     * Returns the identifier used to autosave the field value. If autosave is disabled,
     * an empty string is returned.
     *
     * @since 6.2
     */
    public function getAutosaveId(): string
    {
        return $this->autosaveId;
    }

    /**
     * Sets whether the preview button should be shown or not and returns this form container.
     *
     * By default, the preview button is shown.
     *
     * @return      WysiwygFormContainer            this form container
     * @throws      \BadMethodCallException         if the form field container has already been populated yet
     * @since       5.3
     */
    public function enablePreviewButton(bool $enablePreviewButton = true)
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
     */
    public function getAttachmentField()
    {
        if ($this->attachmentField === null) {
            $this->attachmentField = WysiwygAttachmentFormField::create($this->wysiwygId . 'Attachments');
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
        if ($this->objectId === null) {
            return 0;
        }

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
     */
    public function getWysiwygField()
    {
        if ($this->wysiwygField === null) {
            $this->wysiwygField = WysiwygFormField::create($this->wysiwygId);
        }

        return $this->wysiwygField;
    }

    #[\Override]
    public function id(string $id)
    {
        $this->wysiwygId(\substr($id, 0, -\strlen('Container')));

        return parent::id($id);
    }

    /**
     * Sets the last time the wysiwyg field has been edited and returns this form container.
     *
     * @since 6.2
     */
    public function lastEditTime(int $lastEditTime): static
    {
        $this->lastEditTime = $lastEditTime;

        if ($this->wysiwygField !== null) {
            $this->wysiwygField->lastEditTime($lastEditTime);
        }

        return $this;
    }

    /**
     * Returns the last time the field has been edited. If no last edit time has
     * been set, `0` is returned.
     *
     * @since 6.2
     */
    public function getLastEditTime(): int
    {
        return $this->lastEditTime;
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
     */
    #[\Override]
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
    public function messageObjectType(string $messageObjectType)
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

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true)
    {
        $this->objectId = $object->{$object::getDatabaseTableIndexName()};

        $this->setAttachmentHandler();

        if ($this->loadValueCallback !== null) {
            ($this->loadValueCallback)($object, $this);
        }

        return parent::updatedObject($data, $object, $loadValues);
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
    public function pollObjectType(string $pollObjectType)
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

    #[\Override]
    public function populate()
    {
        parent::populate();

        $this->wysiwygField = $this->getWysiwygField()
            ->objectType($this->messageObjectType)
            ->minimumLength($this->getMinimumLength())
            ->maximumLength($this->getMaximumLength())
            ->required($this->isRequired())
            ->supportMentions($this->supportMentions)
            ->supportQuotes($this->supportQuotes)
            ->autosaveId($this->autosaveId)
            ->lastEditTime($this->lastEditTime);
        $this->smiliesContainer = WysiwygSmileyFormContainer::create($this->wysiwygId . 'SmiliesTab')
            ->wysiwygId($this->getWysiwygId())
            ->label('wcf.message.smilies')
            ->available($this->supportSmilies);
        $this->attachmentField = $this->getAttachmentField()
            ->wysiwygId($this->getWysiwygId());
        $this->settingsContainer = FormContainer::create($this->wysiwygId . 'SettingsContainer')
            ->appendChildren($this->settingsNodes);
        $this->pollContainer = WysiwygPollFormContainer::create($this->wysiwygId . 'PollContainer')
            ->wysiwygId($this->getWysiwygId());
        if ($this->pollObjectType !== null) {
            $this->pollContainer->objectType($this->pollObjectType);
        }

        $this->quoteContainer = WysiwygQuoteFormContainer::create($this->wysiwygId . 'QuoteContainer')
            ->wysiwygId($this->getWysiwygId())
            ->available($this->supportQuotes);

        $this->appendChildren([
            $this->wysiwygField,
            WysiwygTabMenuFormContainer::create($this->wysiwygId . 'Tabs')
                ->attribute('data-preselect', $this->getPreselect())
                ->attribute('data-wysiwyg-container-id', $this->getPrefixedWysiwygId())
                ->useAnchors(false)
                ->appendChildren([
                    $this->smiliesContainer,

                    WysiwygTabFormContainer::create($this->wysiwygId . 'AttachmentsTab')
                        ->addClass('formAttachmentContent')
                        ->label('wcf.attachment.attachments')
                        ->name("attachments")
                        ->icon(FontAwesomeIcon::fromValues('paperclip'))
                        ->wysiwygId($this->getWysiwygId())
                        ->appendChild(
                            FormContainer::create($this->wysiwygId . 'AttachmentsContainer')
                                ->appendChild($this->attachmentField)
                        ),

                    WysiwygTabFormContainer::create($this->wysiwygId . 'SettingsTab')
                        ->label('wcf.message.settings')
                        ->icon(FontAwesomeIcon::fromValues('gear'))
                        ->name('settings')
                        ->wysiwygId($this->getWysiwygId())
                        ->appendChild($this->settingsContainer),

                    WysiwygTabFormContainer::create($this->wysiwygId . 'PollTab')
                        ->label('wcf.poll.management')
                        ->icon(FontAwesomeIcon::fromValues('chart-bar'))
                        ->name('poll')
                        ->wysiwygId($this->getWysiwygId())
                        ->appendChild($this->pollContainer),

                    $this->quoteContainer,
                ]),
        ]);

        if ($this->attachmentData !== null) {
            $this->setAttachmentHandler();
        }
        $this->wysiwygField->supportAttachments($this->attachmentField->isAvailable());

        if ($this->enablePreviewButton && !($this->getDocument() instanceof Psr15DialogForm)) {
            $this->getDocument()->addButton(
                WysiwygPreviewFormButton::create($this->getWysiwygId() . 'PreviewButton')
                    ->objectType($this->messageObjectType)
                    ->wysiwygId($this->getWysiwygId())
                    ->objectId($this->getObjectId())
            );
        }

        EventHandler::getInstance()->fireAction($this, 'populate');

        return $this;
    }

    /**
     * Sets the value of the wysiwyg tab menu's `data-preselect` attribute used to determine which
     * tab is preselected.
     *
     * @param string $preselect id of preselected tab, `'true'` for first tab, or non-existing id for no preselected tab
     * @return  WysiwygFormContainer
     */
    public function preselect(string $preselect = 'true')
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
     *
     * @return  static
     *
     * @deprecated 6.2
     */
    public function quoteData(string $objectType, string $actionClass, array $selectors = [])
    {
        return $this;
    }

    /**
     * Sets whether it is required to fill out the wysiwyg field and returns this container.
     *
     * @param bool $required determines if field has to be filled out
     * @return  static              this container
     */
    public function required(bool $required = true)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Sets the attachment handler of the attachment form field.
     *
     * @return void
     */
    protected function setAttachmentHandler()
    {
        if ($this->attachmentData !== null) {
            $this->attachmentField->attachmentHandler(
                new AttachmentHandler(
                    $this->attachmentData['objectType'],
                    $this->attachmentData['objectID'] ?: $this->getObjectId(),
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
     * @return  WysiwygFormContainer        this form container
     */
    public function supportMentions(bool $supportMentions = true)
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
     * @return  WysiwygFormContainer        this form container
     */
    public function supportQuotes(bool $supportQuotes = true)
    {
        $this->supportQuotes = $supportQuotes;

        if (isset($this->quoteContainer)) {
            $this->quoteContainer->available($supportQuotes);
        }

        $this->wysiwygField?->supportQuotes($supportQuotes);

        return $this;
    }

    /**
     * Sets if smilies are supported for this form container and returns this form container.
     *
     * By default, smilies are supported.
     *
     * @return  WysiwygFormContainer        this form container
     */
    public function supportSmilies(bool $supportSmilies = true)
    {
        if ($this->smiliesContainer !== null) {
            $this->smiliesContainer->available($supportSmilies);
        } else {
            $this->supportSmilies = $supportSmilies;
        }

        return $this;
    }

    #[\Override]
    public function saveValueCallback(\Closure $callback): static
    {
        $this->saveValueCallback = $callback;

        return $this;
    }

    #[\Override]
    public function getSaveValueCallback(): ?\Closure
    {
        return $this->saveValueCallback;
    }

    #[\Override]
    public function loadValueCallback(\Closure $callback): static
    {
        $this->loadValueCallback = $callback;

        return $this;
    }

    #[\Override]
    public function getLoadValueCallback(): ?\Closure
    {
        return $this->loadValueCallback;
    }
}
