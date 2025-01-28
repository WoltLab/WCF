<?php

namespace wcf\system\interaction\admin;

use wcf\data\user\option\UserOption;
use wcf\event\interaction\admin\UserOptionInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserOptionInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('core/users/options/%s', static fn(UserOption $object) => $object->canDelete())
        ]);

        EventHandler::getInstance()->fire(
            new UserOptionInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserOption::class;
    }
}
