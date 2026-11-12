<?php

namespace wcf\system\cronjob;

use wcf\command\blacklist\ImportBlacklist;
use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\data\cronjob\Cronjob;

/**
 * Updates the built-in blacklist data.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class UpdateBlacklistCronjob extends AbstractCronjob
{
    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        if (!BlacklistEntry::isEnabled()) {
            return;
        }

        new ImportBlacklist()();
    }
}
