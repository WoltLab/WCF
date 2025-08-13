<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\event\cronjob\CronjobDisabled;
use wcf\event\IPsr14Event;
use wcf\system\event\EventHandler;

/**
 * Disables a cronjob.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableCronjob implements IPsr14Event
{
    public function __construct(private readonly Cronjob $cronjob) {}

    public function __invoke(): void
    {
        (new CronjobEditor($this->cronjob))->update([
            'isDisabled' => 1,
        ]);

        $event = new CronjobDisabled($this->cronjob);
        EventHandler::getInstance()->fire($event);
    }
}
