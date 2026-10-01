<?php

namespace wcf\system\form\builder\field\wysiwyg;

use wcf\data\IStorableObject;
use wcf\data\language\Language;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\ICensorshipFormField;
use wcf\system\form\builder\field\IL10nFormField;
use wcf\system\form\builder\field\IMaximumLengthFormField;
use wcf\system\form\builder\field\IMinimumLengthFormField;
use wcf\system\form\builder\field\TCensorshipFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\field\TL10nFormField;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\system\message\quote\MessageQuoteManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for wysiwyg editors.
 *
 * The l10n mode processes the value of every language separately and exposes
 * the processed HTML via `getL10nValues()`. It does not register embedded
 * objects, because they are tracked per object rather than per language, thus
 * it cannot be combined with autosave, attachments, mentions or quotes.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class WysiwygFormField extends AbstractFormField implements
    IAttributeFormField,
    ICensorshipFormField,
    IL10nFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    IObjectTypeFormNode
{
    use TCensorshipFormField;
    use TInputAttributeFormField {
        getReservedFieldAttributes as private inputGetReservedFieldAttributes;
    }
    use TL10nFormField {
        getL10nValues as private getRawL10nValues;
        getValue as private i18nGetValue;
        populate as private i18nPopulate;
        readValue as private i18nReadValue;
        validate as private l10nValidate;
        value as private l10nValue;
    }
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TObjectTypeFormNode;

    /**
     * identifier used to autosave the field value; if empty, autosave is disabled
     */
    protected string $autosaveId = '';

    /**
     * input processor containing the wysiwyg text
     */
    protected ?HtmlInputProcessor $htmlInputProcessor = null;

    /**
     * input processors containing the wysiwyg text of each language in l10n mode
     * @var array<int, HtmlInputProcessor>
     * @since 6.3
     */
    protected array $htmlInputProcessors = [];

    /**
     * last time the field has been edited; if `0`, the last edit time is unknown
     */
    protected int $lastEditTime = 0;

    /**
     * is `true` if this form field supports attachments, otherwise `false`
     */
    protected bool $supportAttachments = false;

    /**
     * is `true` if this form field supports mentions, otherwise `false`
     */
    protected bool $supportMentions = false;

    /**
     * is `true` if this form field supports quotes, otherwise `false`
     */
    protected bool $supportQuotes = false;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Ckeditor';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_wysiwygFormField';

    /**
     * Id of the edited object.
     * @since 6.3
     */
    protected ?int $objectID = null;

    public function __construct()
    {
        // WYSIWYG form fields use the censorship function by default for backward compatibility reasons.
        $this->censorship();
    }

    /**
     * Sets the identifier used to autosave the field value and returns this field.
     *
     * @param string $autosaveId identifier used to autosave field value
     */
    public function autosaveId(string $autosaveId): static
    {
        $this->autosaveId = $autosaveId;

        return $this;
    }

    /**
     * Returns the identifier used to autosave the field value. If autosave is disabled,
     * an empty string is returned.
     */
    public function getAutosaveId(): string
    {
        return $this->autosaveId;
    }

    #[\Override]
    public function getFieldHtml(): string
    {
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

    #[\Override]
    public function getObjectTypeDefinition(): string
    {
        return 'com.woltlab.wcf.message';
    }

    /**
     * Returns the last time the field has been edited. If no last edit time has
     * been set, `0` is returned.
     */
    public function getLastEditTime(): int
    {
        return $this->lastEditTime;
    }

    /**
     * Returns all quote data or specific quote data if an argument is given.
     *
     * @throws  \BadMethodCallException     if quotes are not supported for this field
     * @deprecated 6.2
     */
    public function getQuoteData(?string $index = null): string
    {
        if (!$this->supportQuotes) {
            throw new \BadMethodCallException("Quotes are not supported for field '{$this->getId()}'.");
        }

        return "";
    }

    #[\Override]
    public function getSaveValue(): string
    {
        if ($this->isL10n()) {
            throw new \BadMethodCallException(
                "The save value is not available in l10n mode for field '{$this->getId()}', use getL10nValues() instead."
            );
        }

        return $this->htmlInputProcessor->getHtml();
    }

    #[\Override]
    public function hasSaveValue(): bool
    {
        if ($this->isL10n()) {
            return false;
        }

        return true;
    }

    /**
     * @since 6.3
     */
    public function i18n(bool $i18n = true): static
    {
        if ($i18n) {
            throw new \BadMethodCallException(
                "The i18n mode is not supported for field '{$this->getId()}', use l10n() instead."
            );
        }

        return $this;
    }

    /**
     * @since 6.3
     */
    #[\Override]
    public function getL10nValues(): array
    {
        if (!$this->isL10n()) {
            throw new \BadMethodCallException("l10n is not enabled for field '{$this->getId()}'.");
        }

        if ($this->htmlInputProcessors === []) {
            throw new \BadMethodCallException(
                "The l10n values are not available before validate() has been called for field '{$this->getId()}'."
            );
        }

        return \array_map(
            static fn (HtmlInputProcessor $htmlInputProcessor) => $htmlInputProcessor->getHtml(),
            $this->htmlInputProcessors
        );
    }

    /**
     * @since 6.3
     */
    #[\Override]
    public function getJavaScriptDataHandlerModule(): string
    {
        if ($this->isL10n() && \count(LanguageFactory::getInstance()->getLanguages()) > 1) {
            return 'WoltLabSuite/Core/Form/Builder/Field/CkeditorI18n';
        }

        return $this->javaScriptDataHandlerModule;
    }

    /**
     * Sets the last time this field has been edited and returns this field.
     *
     * @param int $lastEditTime last time field has been edited
     */
    public function lastEditTime(int $lastEditTime): static
    {
        $this->lastEditTime = $lastEditTime;

        return $this;
    }

    #[\Override]
    public function populate(): static
    {
        $this->i18nPopulate();

        if ($this->isL10n()) {
            if ($this->autosaveId !== '' || $this->supportAttachments || $this->supportMentions || $this->supportQuotes) {
                throw new \BadMethodCallException(
                    "Autosave, attachments, mentions and quotes are not supported in l10n mode for field '{$this->getId()}'."
                );
            }

            return $this;
        }

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'wysiwyg',
            function (IFormDocument $document, array $parameters) {
                if ($this->checkDependencies()) {
                    $parameters[$this->getObjectProperty() . '_htmlInputProcessor'] = $this->htmlInputProcessor;
                }

                if ($this->supportQuotes) {
                    MessageQuoteManager::getInstance()->saved();
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
     *
     * @deprecated 6.2
     */
    public function quoteData(string $objectType, string $actionClass, array $selectors = []): static
    {
        return $this;
    }

    #[\Override]
    public function readValue(): static
    {
        if ($this->isL10n()) {
            return $this->i18nReadValue();
        }

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
     */
    public function supportAttachments(bool $supportAttachments = true): static
    {
        $this->supportAttachments = $supportAttachments;

        return $this;
    }

    /**
     * Sets if the form field supports mentions and returns this field.
     */
    public function supportMentions(bool $supportMentions = true): static
    {
        $this->supportMentions = $supportMentions;

        return $this;
    }

    /**
     * Sets if the form field supports quotes and returns this field.
     */
    public function supportQuotes(bool $supportQuotes = true): static
    {
        $this->supportQuotes = $supportQuotes;

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
     */
    public function supportsAttachments(): bool
    {
        return $this->supportAttachments;
    }

    /**
     * Returns `true` if the form field supports mentions and returns `false` otherwise.
     *
     * By default, mentions are not supported.
     */
    public function supportsMentions(): bool
    {
        return $this->supportMentions;
    }

    /**
     * Returns `true` if the form field supports quotes and returns `false` otherwise.
     *
     * By default, quotes are not supported.
     */
    public function supportsQuotes(): bool
    {
        return $this->supportQuotes;
    }

    #[\Override]
    public function validate(): void
    {
        $disallowedBBCodesPermission = $this->getObjectType()->disallowedBBCodesPermission;
        if ($disallowedBBCodesPermission === null) {
            $disallowedBBCodesPermission = 'user.message.disallowedBBCodes';
        }

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission($disallowedBBCodesPermission)
        ));

        if ($this->isL10n()) {
            $this->validateL10n();
        } else {
            $this->htmlInputProcessor = new HtmlInputProcessor();
            $this->htmlInputProcessor->process($this->getValue(), $this->getObjectType()->objectType, $this->objectID ?? 0);

            if ($this->isRequired() && $this->htmlInputProcessor->appearsToBeEmpty()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            } else {
                $this->validateHtml($this->htmlInputProcessor);
            }
        }

        parent::validate();
    }

    private function validateL10n(): void
    {
        $this->htmlInputProcessors = [];

        $this->l10nValidate();
        if ($this->getValidationErrors() !== []) {
            return;
        }

        $hasEmptyValue = false;
        foreach ($this->getRawL10nValues() as $languageID => $value) {
            $htmlInputProcessor = new HtmlInputProcessor();
            $htmlInputProcessor->process($value, $this->getObjectType()->objectType, $this->objectID ?? 0);
            $this->htmlInputProcessors[$languageID] = $htmlInputProcessor;

            if ($htmlInputProcessor->appearsToBeEmpty()) {
                $hasEmptyValue = true;
            } else {
                $this->validateHtml(
                    $htmlInputProcessor,
                    $languageID === L10nStorage::MONOLINGUAL ? null : LanguageFactory::getInstance()->getLanguage($languageID)
                );
            }
        }

        // `I18nHandler::validateValue()` only rejects empty strings, but the
        // editor can submit markup that has no visible content.
        if ($hasEmptyValue && $this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError($this->hasPlainValue() ? 'empty' : 'multilingual'));
        }
    }

    /**
     * @param ?Language $language language of the validated text or `null` for monolingual text
     */
    private function validateHtml(HtmlInputProcessor $htmlInputProcessor, ?Language $language = null): void
    {
        $disallowedBBCodes = $htmlInputProcessor->validate();
        if ($disallowedBBCodes !== []) {
            $this->addValidationError(new FormFieldValidationError(
                'disallowedBBCodes',
                'wcf.message.error.disallowedBBCodes',
                ['disallowedBBCodes' => $disallowedBBCodes]
            ));
        } else {
            $message = $htmlInputProcessor->getTextContent();
            if ($message !== '') {
                $this->validateMinimumLength($message, $language);
                $this->validateMaximumLength($message, $language);

                if ($this->getValidationErrors() === []) {
                    $this->validateCensorship($message);
                }
            }
        }
    }

    /**
     * @return string[]
     * @since 5.4
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

    /**
     * @return string|array<int, string>
     */
    #[\Override]
    public function getValue(): string|array
    {
        if ($this->isL10n()) {
            return $this->i18nGetValue();
        }

        return $this->upcast(parent::getValue() ?? '');
    }

    #[\Override]
    public function value(mixed $value): static
    {
        if ($this->isL10n()) {
            // `I18nHandler` passes the stored values to the editor without
            // calling `getValue()`, thus they must be upcast in advance.
            if (\is_string($value)) {
                $value = $this->upcast($value);
            } elseif (\is_array($value)) {
                $value = \array_map(
                    fn ($languageValue) => \is_string($languageValue) ? $this->upcast($languageValue) : $languageValue,
                    $value
                );
            }

            return $this->l10nValue($value);
        }

        return parent::value($value);
    }

    private function upcast(string $html): string
    {
        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($html, $this->getObjectType()->objectType);

        return $upcastProcessor->getHtml();
    }

    /**
     * @since 6.3
     */
    public function getHtmlInputProcessor(): HtmlInputProcessor
    {
        if ($this->htmlInputProcessor === null) {
            throw new \BadMethodCallException("The HTML input processor is not available before validate() has been called.");
        }

        return $this->htmlInputProcessor;
    }

    /**
     * @since 6.3
     */
    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true): static
    {
        $this->objectID = $object->{$object::getDatabaseTableIndexName()};

        return parent::updatedObject($data, $object, $loadValues);
    }
}
