<?php

namespace wcf\system\style\option;

/**
 * Layout of the page footer.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
enum PageFooterLayout implements IStyleOption
{
    case Compact;
    case Classic;

    #[\Override]
    public static function getVariableName(): string
    {
        return 'pageFooterLayout';
    }

    #[\Override]
    public static function getFallback(): static
    {
        return self::Classic;
    }

    #[\Override]
    public static function fromString(string $value): static
    {
        $variant = self::tryFromString($value);
        if ($variant === null) {
            throw new \ValueError("Unknown value '{$value}' for '" . self::getVariableName() . "'.");
        }

        return $variant;
    }

    #[\Override]
    public static function tryFromString(string $value): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->toString() === $value) {
                return $case;
            }
        }

        return null;
    }

    #[\Override]
    public function toString(): string
    {
        return match ($this) {
            self::Classic => 'classic',
            self::Compact => 'compact',
        };
    }

    #[\Override]
    public function isDeprecated(): bool
    {
        return match ($this) {
            self::Classic => true,
            self::Compact => false,
        };
    }

    #[\Override]
    public function getTemplateVariant(): ?string
    {
        return match ($this) {
            self::Classic => null,
            self::Compact => 'pageFooter',
        };
    }

    #[\Override]
    public function getStylesheet(): string
    {
        return match ($this) {
            self::Classic => 'layout/pageFooter/classic.scss',
            self::Compact => 'layout/pageFooter/compact.scss',
        };
    }
}
