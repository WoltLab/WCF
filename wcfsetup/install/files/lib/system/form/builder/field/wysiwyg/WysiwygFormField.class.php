<?php

namespace wcf\system\form\builder\field\wysiwyg;

use wcf\data\IMessageQuoteAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\IMaximumLengthFormField;
use wcf\system\form\builder\field\IMinimumLengthFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\message\censorship\Censorship;
use wcf\system\message\quote\MessageQuoteManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for wysiwyg editors.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class WysiwygFormField extends AbstractFormField implements
    IAttributeFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    IObjectTypeFormNode
{
    use TInputAttributeFormField {
        getReservedFieldAttributes as private inputGetReservedFieldAttributes;
    }
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TObjectTypeFormNode;

    /**
     * identifier used to autosave the field value; if empty, autosave is disabled
     * @var string
     */
    protected $autosaveId = '';

    /**
     * input processor containing the wysiwyg text
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * last time the field has been edited; if `0`, the last edit time is unknown
     * @var int
     */
    protected $lastEditTime = 0;

    /**
     * quote-related data used to create the JavaScript quote manager
     * @var null|array
     */
    protected $quoteData;

    /**
     * is `true` if this form field supports attachments, otherwise `false`
     * @var bool
     */
    protected $supportAttachments = false;

    /**
     * is `true` if this form field supports mentions, otherwise `false`
     * @var bool
     */
    protected $supportMentions = false;

    /**
     * is `true` if this form field supports quotes, otherwise `false`
     * @var bool
     */
    protected $supportQuotes = false;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Ckeditor';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_wysiwygFormField';

    /**
     * Sets the identifier used to autosave the field value and returns this field.
     *
     * @param string $autosaveId identifier used to autosave field value
     * @return  WysiwygFormField        this field
     */
    public function autosaveId($autosaveId)
    {
        $this->autosaveId = $autosaveId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(): static
    {
        MessageQuoteManager::getInstance()->saved();

        return $this;
    }

    /**
     * Returns the identifier used to autosave the field value. If autosave is disabled,
     * an empty string is returned.
     *
     * @return  string
     */
    public function getAutosaveId()
    {
        return $this->autosaveId;
    }

    /**
     * @inheritDoc
     */
    public function getFieldHtml()
    {
        if ($this->supportsQuotes()) {
            MessageQuoteManager::getInstance()->assignVariables();
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $disallowedBBCodesPermission = $this->getObjectType()->disallowedBBCodesPermission;
        if ($disallowedBBCodesPermission === null) {
            $disallowedBBCodesPermission = 'user.message.disallowedBBCodes';
        }

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission($disallowedBBCodesPermission)
        ));

        return parent::getFieldHtml();
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.message';
    }

    /**
     * Returns the last time the field has been edited. If no last edit time has
     * been set, `0` is returned.
     *
     * @return  int
     */
    public function getLastEditTime()
    {
        return $this->lastEditTime;
    }

    /**
     * Returns all quote data or specific quote data if an argument is given.
     *
     * @param null|string $index quote data index
     * @return  string[]|string
     *
     * @throws  \BadMethodCallException     if quotes are not supported for this field
     * @throws  \InvalidArgumentException   if unknown quote data is requested
     */
    public function getQuoteData($index = null)
    {
        if (!$this->supportQuotes()) {
            throw new \BadMethodCallException("Quotes are not supported for field '{$this->getId()}'.");
        }

        if ($index === null) {
            return $this->quoteData;
        }

        if (!isset($this->quoteData[$index])) {
            throw new \InvalidArgumentException("Unknown quote data '{$index}' for field '{$this->getId()}'.");
        }

        return $this->quoteData[$index];
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        return $this->htmlInputProcessor->getHtml();
    }

    /**
     * Sets the last time this field has been edited and returns this field.
     *
     * @param int $lastEditTime last time field has been edited
     * @return  WysiwygFormField    this field
     */
    public function lastEditTime($lastEditTime)
    {
        $this->lastEditTime = $lastEditTime;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'wysiwyg',
            function (IFormDocument $document, array $parameters) {
                if ($this->checkDependencies()) {
                    $parameters[$this->getObjectProperty() . '_htmlInputProcessor'] = $this->htmlInputProcessor;
                }

                return $parameters;
            }
        ));

        return $this;
    }

    /**
     * Sets the data required for advanced quote support for when quotable content is present
     * on the active page and returns this field.
     *
     * Calling this method automatically enables quote support for this field.
     *
     * @param string $objectType name of the relevant `com.woltlab.wcf.message.quote` object type
     * @param string $actionClass action class implementing `wcf\data\IMessageQuoteAction`
     * @param string[] $selectors selectors for the quotable content (required keys: `container`, `messageBody`, and `messageContent`)
     * @return  static
     *
     * @throws  \InvalidArgumentException   if any of the given arguments is invalid
     */
    public function quoteData($objectType, $actionClass, array $selectors = [])
    {
        if (
            ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.message.quote',
                $objectType
            ) === null
        ) {
            throw new \InvalidArgumentException(
                "Unknown message quote object type '{$objectType}' for field '{$this->getId()}'."
            );
        }

        if (!\class_exists($actionClass)) {
            throw new \InvalidArgumentException("Unknown class '{$actionClass}' for field '{$this->getId()}'.");
        }
        if (!\is_subclass_of($actionClass, IMessageQuoteAction::class)) {
            throw new \InvalidArgumentException(
                "'{$actionClass}' does not implement '" . IMessageQuoteAction::class . "' for field '{$this->getId()}'."
            );
        }

        if (!empty($selectors)) {
            foreach (['container', 'messageBody', 'messageContent'] as $selector) {
                if (!isset($selectors[$selector])) {
                    throw new \InvalidArgumentException("Missing selector '{$selector}' for field '{$this->getId()}'.");
                }
            }
        }

        $this->supportQuotes();

        $this->quoteData = [
            'actionClass' => $actionClass,
            'objectType' => $objectType,
            'selectors' => $selectors,
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = StringUtil::trim($value);
            }
        }

        if ($this->supportsQuotes()) {
            MessageQuoteManager::getInstance()->readFormParameters();
        }

        return $this;
    }

    /**
     * Sets if the form field supports attachments and returns this field.
     *
     * @param bool $supportAttachments
     * @return  WysiwygFormField        this field
     */
    public function supportAttachments($supportAttachments = true)
    {
        $this->supportAttachments = $supportAttachments;

        return $this;
    }

    /**
     * Sets if the form field supports mentions and returns this field.
     *
     * @param bool $supportMentions
     * @return  WysiwygFormField        this field
     */
    public function supportMentions($supportMentions = true)
    {
        $this->supportMentions = $supportMentions;

        return $this;
    }

    /**
     * Sets if the form field supports quotes and returns this field.
     *
     * @param bool $supportQuotes
     * @return  WysiwygFormField        this field
     */
    public function supportQuotes($supportQuotes = true)
    {
        $this->supportQuotes = $supportQuotes;

        if (!$this->supportsQuotes()) {
            // unset previously set quote data
            $this->quoteData = null;
        } else {
            MessageQuoteManager::getInstance()->readParameters();
        }

        return $this;
    }

    /**
     * Returns `true` if the form field supports attachments and returns `false` otherwise.
     *
     * Important: If this method returns `true`, it does not necessarily mean that attachment
     * support will also work as that is the task of `WysiwygAttachmentFormField`. This method
     * is primarily relevant to inform the JavaScript API that the field supports attachments
     * so that the relevant editor plugin is loaded.
     *
     * By default, attachments are not supported.
     *
     * @return  bool
     */
    public function supportsAttachments()
    {
        return $this->supportAttachments;
    }

    /**
     * Returns `true` if the form field supports mentions and returns `false` otherwise.
     *
     * By default, mentions are not supported.
     *
     * @return  bool
     */
    public function supportsMentions()
    {
        return $this->supportMentions;
    }

    /**
     * Returns `true` if the form field supports quotes and returns `false` otherwise.
     *
     * By default, quotes are not supported.
     *
     * @return  bool
     */
    public function supportsQuotes()
    {
        return $this->supportQuotes;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $disallowedBBCodesPermission = $this->getObjectType()->disallowedBBCodesPermission;
        if ($disallowedBBCodesPermission === null) {
            $disallowedBBCodesPermission = 'user.message.disallowedBBCodes';
        }

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission($disallowedBBCodesPermission)
        ));

        $this->htmlInputProcessor = new HtmlInputProcessor();
        $this->htmlInputProcessor->process($this->getValue(), $this->getObjectType()->objectType);

        if ($this->isRequired() && $this->htmlInputProcessor->appearsToBeEmpty()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } else {
            $disallowedBBCodes = $this->htmlInputProcessor->validate();
            if (!empty($disallowedBBCodes)) {
                $this->addValidationError(new FormFieldValidationError(
                    'disallowedBBCodes',
                    'wcf.message.error.disallowedBBCodes',
                    ['disallowedBBCodes' => $disallowedBBCodes]
                ));
            } else {
                $message = $this->htmlInputProcessor->getTextContent();
                if ($message !== '') {
                    $this->validateMinimumLength($message);
                    $this->validateMaximumLength($message);

                    if (empty($this->getValidationErrors())) {
                        $censoredWords = Censorship::getInstance()->test($message);
                        if ($censoredWords) {
                            $this->addValidationError(new FormFieldValidationError(
                                'censoredWords',
                                'wcf.message.error.censoredWordsFound',
                                ['censoredWords' => $censoredWords]
                            ));
                        }
                    }
                }
            }
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     * @since       5.4
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            static::inputGetReservedFieldAttributes(),
            [
                'data-autosave',
                'data-autosave-last-edit-time',
                'data-disable-attachments',
                'data-support-mention',
            ]
        );
    }

    #[\Override]
    public function getValue()
    {
        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process(parent::getValue() ?? '', $this->getObjectType()->objectType);
        return $upcastProcessor->getHtml();
    }
}
