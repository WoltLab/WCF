<?php

namespace wcf\command\ad;

use wcf\data\ad\Ad;
use wcf\data\ad\AdBuilder;
use wcf\event\ad\AdCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new ad.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateAd
{
    public function __construct(
        private readonly AdBuilder $builder,
    ) {}

    public function __invoke(): Ad
    {
        $ad = $this->builder->create();

        AdBuilder::resetCache();

        EventHandler::getInstance()->fire(new AdCreated($ad, $this->builder));

        return $ad;
    }
}
