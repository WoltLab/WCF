<?php

namespace wcf\command\ad;

use wcf\data\ad\Ad;
use wcf\data\ad\AdBuilder;
use wcf\event\ad\AdUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates an ad.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateAd
{
    public function __construct(
        private readonly AdBuilder $builder,
    ) {}

    public function __invoke(): Ad
    {
        $ad = $this->builder->update();

        AdBuilder::resetCache();

        EventHandler::getInstance()->fire(new AdUpdated($ad, $this->builder));

        return $ad;
    }
}
