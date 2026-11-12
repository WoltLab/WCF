<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Implementation of a badge color form field for selecting a single color or a custom color.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @since       6.2
 */
final class BadgeColorFormField extends RadioButtonFormField implements IPatternFormField
{
    use TPatternFormField;

    public const CUSTOM_CSS_CLASSNAME = 'custom';
    public const AVAILABLE_CSS_CLASSNAMES = [
        'yellow',
        'orange',
        'brown',
        'red',
        'pink',
        'purple',
        'blue',
        'green',
        'black',

        'none', /* not a real value */
        BadgeColorFormField::CUSTOM_CSS_CLASSNAME, /* not a real value */
    ];

    /**
     * Colors that are only available for labels (`labelBadge`).
     *
     * @since 6.3
     */
    public const LABEL_CSS_CLASSNAMES = [
        'teal',
        'lime',
        'navy',
        'maroon',
    ];

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_badgeColorFormField';

    protected ?string $textReferenceNodeId = null;
    protected string $defaultLabelText;
    protected string $customClassName = '';
    protected string $previewClassName = 'badge';

    public function __construct()
    {
        parent::__construct();

        // the colors are laid out by `.labelSelection`
        $this
            ->removeClass('floated')
            ->addFieldClass('labelSelection__radio')
            ->defaultLabelText(WCF::getLanguage()->get('wcf.acp.label.defaultValue'))
            ->options(\array_combine(self::AVAILABLE_CSS_CLASSNAMES, self::AVAILABLE_CSS_CLASSNAMES))
            ->pattern('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$');
    }

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = StringUtil::trim($this->getDocument()->getRequestData($this->getPrefixedId()));

            if ($this->supportsCustomClassName() && $this->value === BadgeColorFormField::CUSTOM_CSS_CLASSNAME) {
                $this->customClassName = StringUtil::trim(
                    $this->getDocument()->getRequestData($this->getPrefixedId() . 'customCssClassName')
                );
            }
        }

        return $this;
    }

    #[\Override]
    public function validate()
    {
        if ($this->supportsCustomClassName() && $this->getValue() === BadgeColorFormField::CUSTOM_CSS_CLASSNAME) {
            if (Regex::compile($this->getPattern())->match($this->customClassName) === 0) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        'invalid',
                        'wcf.global.form.error.invalidCssClassName'
                    )
                );
            }
        } else {
            parent::validate();
        }
    }

    #[\Override]
    public function value(mixed $value)
    {
        if ($this->supportsCustomClassName() && !\in_array($value, $this->options, true)) {
            parent::value(BadgeColorFormField::CUSTOM_CSS_CLASSNAME);
            $this->customClassName = $value;
        } else {
            parent::value($value);
        }

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getSaveValue()
    {
        if ($this->hasCustomClassName()) {
            return $this->getCustomClassName();
        }

        return $this->getValue();
    }

    public function defaultLabelText(string $text): self
    {
        $this->defaultLabelText = $text;

        return $this;
    }

    /**
     * Sets the form field whose value is used as the text of the color previews.
     */
    public function textReferenceNode(IFormField $field): self
    {
        return $this->textReferenceNodeId($field->getId());
    }

    /**
     * Sets the id of the form field whose value is used as the text of the color
     * previews. The prefix of the form document is prepended when the id is read.
     */
    public function textReferenceNodeId(string $id): self
    {
        $this->textReferenceNodeId = $id;

        return $this;
    }

    /**
     * Sets the CSS class of the color previews, `badge` for badges such as user
     * ranks and `labelBadge` for labels.
     *
     * @since 6.3
     */
    public function previewClassName(string $className): self
    {
        $this->previewClassName = $className;

        return $this;
    }

    /**
     * Adds colors in front of the `none` and custom options, e.g.
     * `BadgeColorFormField::LABEL_CSS_CLASSNAMES` for labels.
     *
     * @param list<string> $classNames
     * @since 6.3
     */
    public function additionalColors(array $classNames): self
    {
        $options = \array_keys($this->options);
        $index = \array_search('none', $options, true);
        \array_splice($options, $index === false ? \count($options) : $index, 0, $classNames);

        return $this->options(\array_combine($options, $options));
    }

    /**
     * @since 6.3
     */
    public function getPreviewClassName(): string
    {
        return $this->previewClassName;
    }

    public function getDefaultLabelText(): string
    {
        return $this->defaultLabelText;
    }

    public function getTextReferenceNodeId(): ?string
    {
        if ($this->textReferenceNodeId === null) {
            return null;
        }

        return $this->getDocument()->getPrefix() . $this->textReferenceNodeId;
    }

    public function hasCustomClassName(): bool
    {
        return $this->supportsCustomClassName() && $this->value === BadgeColorFormField::CUSTOM_CSS_CLASSNAME;
    }

    public function getCustomClassName(): string
    {
        return $this->customClassName;
    }

    /**
     * Sets whether the custom class name is supported.
     */
    public function supportCustomClassName(bool $supportCustomClassName = true): self
    {
        $classNames = \array_keys($this->options);

        if ($supportCustomClassName) {
            // already supported
            if ($this->supportsCustomClassName()) {
                return $this;
            }

            $classNames[] = BadgeColorFormField::CUSTOM_CSS_CLASSNAME;
        } else {
            $classNames = \array_filter($classNames, function ($className) {
                return $className !== BadgeColorFormField::CUSTOM_CSS_CLASSNAME;
            });
        }

        return $this
            ->options(\array_combine($classNames, $classNames));
    }

    /**
     * Returns whether the custom class name is supported.
     */
    public function supportsCustomClassName(): bool
    {
        return \in_array(BadgeColorFormField::CUSTOM_CSS_CLASSNAME, $this->options, true);
    }

    #[\Override]
    public function getLabelTarget(): ?string
    {
        return null;
    }
}
