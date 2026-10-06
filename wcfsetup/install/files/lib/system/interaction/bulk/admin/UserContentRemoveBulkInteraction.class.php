<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\acp\action\UserContentRemoveAction;
use wcf\data\user\User;
use wcf\system\interaction\bulk\BulkFormBuilderDialogInteraction;
use wcf\util\StringUtil;

/**
 * Selects the content of the selected users in a dialog and removes it with a worker.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserContentRemoveBulkInteraction extends BulkFormBuilderDialogInteraction
{
    public function __construct()
    {
        parent::__construct(
            'remove-content',
            UserContentRemoveAction::class,
            'wcf.acp.content.removeContent',
            static fn(User $user) => $user->canDelete()
        );
    }

    #[\Override]
    public function renderInitialization(string $containerId): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $containerId = StringUtil::encodeJS($containerId);

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Acp/Component/User/RemoveContent'], ({ setupBulk }) => {
                    setupBulk('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
