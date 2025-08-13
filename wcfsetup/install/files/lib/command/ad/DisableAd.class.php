<?php

namespace wcf\command\ad;

use wcf\data\ad\Ad;
use wcf\data\ad\AdEditor;
use wcf\event\ad\AdDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables an ad.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableAd
{
    public function __construct(
        private readonly Ad $ad
    ) {}

    public function __invoke(): void
    {
        (new AdEditor($this->ad))->update([
            'isDisabled' => 1,
        ]);

        $event = new AdDisabled($this->ad);
        EventHandler::getInstance()->fire($event);
    }
}
