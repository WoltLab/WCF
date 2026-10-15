<?php

namespace wcf\system\form\builder\field\trophy;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\style\FontAwesomeIcon;

/**
 * Form field to configure the icon, the icon color, the background color and the
 * border color of a badge trophy.
 *
 * The value is an array with the keys `iconName`, `iconColor`, `badgeColor` and
 * `borderColor`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TrophyBadgeFormField extends AbstractFormField implements IImmutableFormField
{
    use TImmutableFormField;

    public const DEFAULT_ICON_NAME = 'trophy;false';

    public const DEFAULT_ICON_COLOR = 'rgba(255, 255, 255, 1)';

    public const DEFAULT_BADGE_COLOR = 'rgba(50, 92, 132, 1)';

    public const DEFAULT_BORDER_COLOR = 'rgba(0, 0, 0, 0)';

    private const COLOR_PATTERN = '~^rgba\(\d{1,3}, ?\d{1,3}, ?\d{1,3}, ?(1|1\.00?|0|0?\.[0-9]{1,2})\)$~';

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/TrophyBadge';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_trophyBadgeFormField';

    /**
     * @inheritDoc
     */
    protected $value = [
        'iconName' => self::DEFAULT_ICON_NAME,
        'iconColor' => self::DEFAULT_ICON_COLOR,
        'badgeColor' => self::DEFAULT_BADGE_COLOR,
        'borderColor' => self::DEFAULT_BORDER_COLOR,
    ];

    /**
     * `true` if the global icon-related JavaScript code has not been included yet.
     */
    private static bool $includeJavaScript = true;

    #[\Override]
    public function getHtmlVariables(): array
    {
        $includeJavaScript = self::$includeJavaScript;
        self::$includeJavaScript = false;

        return [
            '__trophyBadgeFormFieldIncludeJavaScript' => $includeJavaScript,
        ];
    }

    #[\Override]
    public function readValue(): TrophyBadgeFormField
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());
            if (\is_array($value)) {
                $this->value = [
                    'iconName' => (string)($value['iconName'] ?? ''),
                    'iconColor' => (string)($value['iconColor'] ?? ''),
                    'badgeColor' => (string)($value['badgeColor'] ?? ''),
                    'borderColor' => (string)($value['borderColor'] ?? ''),
                ];
            }
        }

        return $this;
    }

    #[\Override]
    public function value(mixed $value): TrophyBadgeFormField
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(
                "Given value is no array, '" . \gettype($value) . "' given for field '{$this->getId()}'."
            );
        }

        $this->value = [
            'iconName' => (string)($value['iconName'] ?? self::DEFAULT_ICON_NAME),
            'iconColor' => (string)($value['iconColor'] ?? self::DEFAULT_ICON_COLOR),
            'badgeColor' => (string)($value['badgeColor'] ?? self::DEFAULT_BADGE_COLOR),
            'borderColor' => (string)($value['borderColor'] ?? self::DEFAULT_BORDER_COLOR),
        ];

        return $this;
    }

    #[\Override]
    public function validate(): void
    {
        parent::validate();

        if ($this->getIconName() === '') {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }

            return;
        }

        if (!FontAwesomeIcon::isValidString($this->getIconName())) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));

            return;
        }

        if (
            \preg_match(self::COLOR_PATTERN, $this->getIconColor()) !== 1
            || \preg_match(self::COLOR_PATTERN, $this->getBadgeColor()) !== 1
            || \preg_match(self::COLOR_PATTERN, $this->getBorderColor()) !== 1
        ) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.style.colorPicker.error.invalidColor'
            ));
        }
    }

    public function getIconName(): string
    {
        return $this->value['iconName'];
    }

    public function getIconColor(): string
    {
        return $this->value['iconColor'];
    }

    public function getBadgeColor(): string
    {
        return $this->value['badgeColor'];
    }

    public function getBorderColor(): string
    {
        return $this->value['borderColor'];
    }

    /**
     * Returns the selected icon or the default icon if the selection is invalid.
     */
    public function getIcon(): FontAwesomeIcon
    {
        if (FontAwesomeIcon::isValidString($this->getIconName())) {
            return FontAwesomeIcon::fromString($this->getIconName());
        }

        return FontAwesomeIcon::fromString(self::DEFAULT_ICON_NAME);
    }
}
