<?php

namespace wcf\system\style\option;

/**
 * A per-style option stored as a style variable. Each value selects the
 * template variant and the stylesheet that the style uses for this area.
 *
 * The style editor renders each value with a schematic preview from the ACP
 * template `__styleOptionPreview_<variableName>`, which receives the value as
 * `$styleOptionValue`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
interface IStyleOption extends \UnitEnum
{
    /**
     * Returns the name of the style variable that stores this option.
     */
    public static function getVariableName(): string;

    /**
     * Returns the value for imported styles that do not set this option or
     * set a value that is not valid for it, typically styles that predate
     * the option.
     */
    public static function getFallback(): static;

    /**
     * Returns the case for the stored value, throwing a `\ValueError` for
     * values that are not valid for this option.
     */
    public static function fromString(string $value): static;

    /**
     * Returns the case for the stored value or `null` if it is not valid for
     * this option.
     */
    public static function tryFromString(string $value): ?static;

    /**
     * Returns the value stored in the style variable.
     */
    public function toString(): string;

    /**
     * Returns true if this value is scheduled for removal in a future major
     * release.
     */
    public function isDeprecated(): bool;

    /**
     * Returns the template variant this value activates or `null` for none.
     *
     * The variant name is the name of the template that is replaced by its
     * `system_` counterpart, see `TemplateEngine::setTemplateVariants()`.
     */
    public function getTemplateVariant(): ?string;

    /**
     * Returns the stylesheet for this value, relative to `WCF_DIR . 'style/'`.
     *
     * The stylesheet must live in a subdirectory of a top-level style
     * directory, e.g. `layout/pageHeader/system.scss`, because those are not
     * picked up by `StyleCompiler::getCoreFiles()` on their own.
     */
    public function getStylesheet(): string;
}
