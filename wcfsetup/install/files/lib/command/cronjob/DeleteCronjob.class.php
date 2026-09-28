<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobBuilder;
use wcf\event\cronjob\CronjobDeleted;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Deletes a cronjob.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteCronjob
{
    public function __construct(
        private readonly Cronjob $cronjob,
    ) {}

    public function __invoke(): void
    {
        CronjobBuilder::delete($this->cronjob);

        CronjobCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new CronjobDeleted($this->cronjob));
    }
}
