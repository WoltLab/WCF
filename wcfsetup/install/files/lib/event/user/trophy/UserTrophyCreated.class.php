<?php

namespace wcf\event\user\trophy;

use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user trophy has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserTrophyCreated implements IPsr14Event
{
    public function __construct(
        public readonly UserTrophy $userTrophy,
        public readonly UserTrophyBuilder $builder,
    ) {}
}
