<?php

namespace wcf\event\user\trophy;

use wcf\data\user\trophy\UserTrophy;
use wcf\event\IPsr14Event;

/**
 * Indicates that user trophies have been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserTrophiesDeleted implements IPsr14Event
{
    /**
     * @param UserTrophy[] $userTrophies
     */
    public function __construct(
        public readonly array $userTrophies,
    ) {}
}
