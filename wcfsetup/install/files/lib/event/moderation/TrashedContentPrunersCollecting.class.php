<?php

namespace wcf\event\moderation;

use wcf\event\IPsr14Event;
use wcf\system\moderation\ITrashedContentPruner;

/**
 * Requests the collection of pruners that permanently delete trashed content.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TrashedContentPrunersCollecting implements IPsr14Event
{
    /**
     * @var list<ITrashedContentPruner>
     */
    private array $pruners = [];

    public function register(ITrashedContentPruner $pruner): void
    {
        $this->pruners[] = $pruner;
    }

    /**
     * @return list<ITrashedContentPruner>
     */
    public function getPruners(): array
    {
        return $this->pruners;
    }
}
