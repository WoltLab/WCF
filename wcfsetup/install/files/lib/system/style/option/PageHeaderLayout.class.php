<?php

namespace wcf\system\style\option;

/**
 * Layout of the page header.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
enum PageHeaderLayout implements IStyleOption
{
    case Classic;
    case LogoTop;
    case LogoBelow;
    case LogoInBar;

    #[\Override]
    public static function getVariableName(): string
    {
        return 'pageHeaderLayout';
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
            self::LogoTop => 'logoTop',
            self::LogoBelow => 'logoBelow',
            self::LogoInBar => 'logoInBar',
        };
    }

    #[\Override]
    public function isDeprecated(): bool
    {
        return match ($this) {
            self::Classic => true,
            default => false,
        };
    }

    #[\Override]
    public function getTemplateVariant(): ?string
    {
        return match ($this) {
            self::Classic => null,
            default => 'pageHeader',
        };
    }

    #[\Override]
    public function getStylesheet(): string
    {
        return match ($this) {
            self::Classic => 'layout/pageHeader/classic.scss',
            default => 'layout/pageHeader/system.scss',
        };
    }
}
