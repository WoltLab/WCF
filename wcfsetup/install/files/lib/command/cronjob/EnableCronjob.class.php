<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\event\cronjob\CronjobEnabled;
use wcf\event\IPsr14Event;
use wcf\system\event\EventHandler;

/**
 * Enables the given cronjob.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableCronjob implements IPsr14Event
{
    public function __construct(private readonly Cronjob $cronjob) {}

    public function __invoke(): void
    {
        (new CronjobEditor($this->cronjob))->update([
            'isDisabled' => 0,
        ]);

        $event = new CronjobEnabled($this->cronjob);
        EventHandler::getInstance()->fire($event);
    }
}
