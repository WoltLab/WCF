<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\user\rank\UserRankList;
use wcf\event\interaction\bulk\admin\UserRankBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;

/**
 * Bulk interaction provider for user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserRankBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkDeleteInteraction('core/users/ranks/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new UserRankBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return UserRankList::class;
    }
}
