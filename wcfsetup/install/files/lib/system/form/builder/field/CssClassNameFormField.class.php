<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Implementation of a css classname form field for selecting a single class name or a custom classname.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CssClassNameFormField extends RadioButtonFormField implements IPatternFormField
{
    use TPatternFormField;

    public const CUSTOM_CSS_CLASSNAME = 'custom';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_cssClassnameFormField';

    private string $customClassName = '';
    private string $visualTemplate = '<div class="{$className}">{$label}</div>';

    public function __construct()
    {
        $this
            ->addClass('inlineList')
            ->addFieldClass('classNameSelection__radio')
            ->pattern('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$');
    }

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = StringUtil::trim($this->getDocument()->getRequestData($this->getPrefixedId()));

            if ($this->supportsCustomClassName() && $this->value === self::CUSTOM_CSS_CLASSNAME) {
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
        if ($this->supportsCustomClassName() && $this->getValue() === self::CUSTOM_CSS_CLASSNAME) {
            if (!Regex::compile($this->getPattern())->match($this->customClassName)) {
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
    public function value($value)
    {
        if ($this->supportsCustomClassName() && !\array_key_exists($value, $this->options)) {
            parent::value(self::CUSTOM_CSS_CLASSNAME);
            $this->customClassName = $value;
        } else {
            parent::value($value);
        }

        return $this;
    }

    #[\Override]
    public function getSaveValue()
    {
        if ($this->hasCustomClassName()) {
            return $this->getCustomClassName();
        }

        return $this->getValue();
    }

    public function hasCustomClassName(): bool
    {
        return $this->supportsCustomClassName() && $this->value === self::CUSTOM_CSS_CLASSNAME;
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
        $options = $this->options;

        if ($supportCustomClassName) {
            // already supported
            if ($this->supportsCustomClassName()) {
                return $this;
            }

            $options[self::CUSTOM_CSS_CLASSNAME] = '';
        } else {
            unset($options[self::CUSTOM_CSS_CLASSNAME]);
        }

        return $this->options($options);
    }

    /**
     * Returns whether the custom class name is supported.
     */
    public function supportsCustomClassName(): bool
    {
        return \array_key_exists(self::CUSTOM_CSS_CLASSNAME, $this->options);
    }

    public function visualTemplate(string $visualTemplate): self
    {
        $this->visualTemplate = $visualTemplate;

        return $this;
    }

    public function getVisualTemplate(): string
    {
        return $this->visualTemplate;
    }

    public function renderVisualTemplate(string $className, string $label): string
    {
        return WCF::getTPL()->fetchString(
            WCF::getTPL()->getCompiler()->compileString('visualTemplate', $this->visualTemplate)['template'],
            [
                'className' => $className,
                'label' => $label,
            ]
        );
    }
}
