<?php

namespace wcf\command\ad;

use wcf\data\ad\Ad;
use wcf\data\ad\AdBuilder;
use wcf\event\ad\AdDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes an ad.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteAd
{
    public function __construct(
        private readonly Ad $ad,
    ) {}

    public function __invoke(): void
    {
        AdBuilder::delete($this->ad);

        AdBuilder::resetCache();

        EventHandler::getInstance()->fire(new AdDeleted($this->ad));
    }
}
