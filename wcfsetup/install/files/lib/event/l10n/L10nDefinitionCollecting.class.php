<?php

namespace wcf\event\l10n;

use wcf\event\IPsr14Event;
use wcf\system\l10n\L10nDefinition;

/**
 * Collects the localization definitions that support the synchronization with
 * language variables. The collected definitions are synchronized at the end of
 * a package installation or update.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nDefinitionCollecting implements IPsr14Event
{
    /**
     * @var list<L10nDefinition>
     */
    private array $definitions = [];

    public function register(L10nDefinition $definition): void
    {
        if (!$definition->supportsLanguageItemSync()) {
            throw new \InvalidArgumentException(
                "The l10n definition of '{$definition->l10nTableName}' does not support the synchronization with language variables."
            );
        }

        $this->definitions[] = $definition;
    }

    /**
     * @return list<L10nDefinition>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
