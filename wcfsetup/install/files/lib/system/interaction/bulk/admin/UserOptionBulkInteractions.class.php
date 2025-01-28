<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionList;
use wcf\event\interaction\bulk\admin\UserOptionBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;

/**
 * Bulk interaction provider for user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserOptionBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkDeleteInteraction('core/users/options/%s', static fn(UserOption $object) => $object->canDelete()),
        ]);

        EventHandler::getInstance()->fire(
            new UserOptionBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return UserOptionList::class;
    }
}
