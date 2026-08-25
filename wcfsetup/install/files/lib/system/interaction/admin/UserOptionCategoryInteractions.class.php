<?php

namespace wcf\system\interaction\admin;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\event\interaction\admin\UserOptionCategoryInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for user option categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionCategoryInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction(
                'core/users/options/categories/%s',
                static fn(UserOptionCategory $object) => $object->canDelete()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new UserOptionCategoryInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserOptionCategory::class;
    }
}
