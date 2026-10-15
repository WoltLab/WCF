<?php

namespace wcf\command\trophy;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\event\trophy\TrophyUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates an existing trophy.
 *
 * Changing the disabled state also updates the trophy points and the special
 * trophies of the users who have received the trophy.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateTrophy
{
    public function __construct(
        private readonly TrophyBuilder $builder,
    ) {}

    public function __invoke(): Trophy
    {
        $wasDisabled = $this->builder->getObject()->isDisabled;

        $trophy = $this->builder->update();

        TrophyBuilder::resetCache();

        if ($trophy->isDisabled !== $wasDisabled) {
            if ($trophy->isDisabled !== 0) {
                new DisableTrophy($trophy)();
            } else {
                new EnableTrophy($trophy)();
            }
        }

        EventHandler::getInstance()->fire(new TrophyUpdated($trophy, $this->builder));

        return $trophy;
    }
}
