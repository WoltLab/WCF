<?php

namespace wcf\system\gridView;

use wcf\data\DatabaseObject;

/**
 * Provides an abstract implementation of a grid view row link.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractGridViewRowLink implements IGridViewRowLink
{
    public function __construct(
        protected readonly ?\Closure $isAvailableCallback = null,
    ) {}

    #[\Override]
    public function renderInitialization(string $containerId): ?string
    {
        return null;
    }

    #[\Override]
    public function isAvailable(DatabaseObject $row): bool
    {
        if ($this->isAvailableCallback === null) {
            return true;
        }

        return ($this->isAvailableCallback)($row);
    }
}
