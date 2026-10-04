<?php

namespace wcf\system\interaction\admin;

use wcf\acp\action\UserContentRemoveAction;
use wcf\data\user\UserProfile;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\user\UserAdministrationPolicy;
use wcf\util\StringUtil;

/**
 * Selects the content of a user in a dialog and removes it with a worker.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserContentRemoveInteraction extends FormBuilderDialogInteraction
{
    public function __construct()
    {
        parent::__construct(
            'remove-content',
            LinkHandler::getInstance()->getControllerLink(UserContentRemoveAction::class, ['id' => '%s']),
            'wcf.acp.content.removeContent',
            static fn(UserProfile $user) => UserAdministrationPolicy::canDelete($user->getDecoratedObject())
        );
    }

    #[\Override]
    public function renderInitialization(string $containerId): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $containerId = StringUtil::encodeJS($containerId);

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Acp/Component/User/RemoveContent'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
