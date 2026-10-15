<?php

namespace wcf\command\trophy;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\event\trophy\TrophyCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new trophy.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateTrophy
{
    public function __construct(
        private readonly TrophyBuilder $builder,
    ) {}

    public function __invoke(): Trophy
    {
        $trophy = $this->builder->create();

        TrophyBuilder::resetCache();

        EventHandler::getInstance()->fire(new TrophyCreated($trophy, $this->builder));

        return $trophy;
    }
}
