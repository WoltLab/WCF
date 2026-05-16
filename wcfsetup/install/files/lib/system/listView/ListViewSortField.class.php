<?php

namespace wcf\system\listView;

use wcf\system\WCF;

/**
 * Represents a sort field of a list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ListViewSortField implements \Stringable
{
    public function __construct(
        public readonly string $id,
        public readonly string $languageItem,
        public readonly string $sortByDatabaseColumn = ''
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return WCF::getLanguage()->get($this->languageItem);
    }
}
