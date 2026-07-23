<?php

namespace wcf\event\l10n;

use wcf\event\IPsr14Event;
use wcf\system\l10n\L10nDefinition;

/**
 * Collects the localization definitions of database objects.
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
