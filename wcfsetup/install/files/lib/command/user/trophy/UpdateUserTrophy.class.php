<?php

namespace wcf\command\user\trophy;

use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\event\user\trophy\UserTrophyUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates an existing user trophy.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUserTrophy
{
    public function __construct(
        private readonly UserTrophyBuilder $builder,
    ) {}

    public function __invoke(): UserTrophy
    {
        $userTrophy = $this->builder->update();

        EventHandler::getInstance()->fire(new UserTrophyUpdated($userTrophy, $this->builder));

        return $userTrophy;
    }
}
