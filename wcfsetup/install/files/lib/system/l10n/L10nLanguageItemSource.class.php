<?php

namespace wcf\system\l10n;

/**
 * Describes the source of a single localized column when migrating existing
 * data into an `*_l10n` table via `L10nLanguageItemSync::migrate()`.
 *
 * The value is either taken from the language variable `$languageItem` (if set
 * and the phrase exists) or from the literal `$literal` fallback. When
 * `$deleteAfterMigration` is set, the language variable is removed once the
 * migration has completed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nLanguageItemSource
{
    public function __construct(
        public readonly ?string $languageItem = null,
        public readonly ?string $literal = null,
        public readonly bool $deleteAfterMigration = false,
    ) {}
}
