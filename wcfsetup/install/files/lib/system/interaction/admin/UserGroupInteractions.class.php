<?php

namespace wcf\system\interaction\admin;

use wcf\acp\action\UserGroupCopyAction;
use wcf\data\user\group\UserGroup;
use wcf\event\interaction\admin\UserGroupInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\InteractionEffect;
use wcf\system\request\LinkHandler;

/**
 * Interaction provider for user groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserGroupInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction("core/users/groups/%s", static fn(UserGroup $group) => $group->isDeletable()),
            new FormBuilderDialogInteraction(
                'copy',
                LinkHandler::getInstance()->getControllerLink(UserGroupCopyAction::class, ['id' => '%s']),
                'wcf.acp.group.button.copy',
                static function (UserGroup $group): bool {
                    return $group->canCopy();
                },
                InteractionEffect::ReloadList
            )
        ]);

        EventHandler::getInstance()->fire(
            new UserGroupInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserGroup::class;
    }
}
