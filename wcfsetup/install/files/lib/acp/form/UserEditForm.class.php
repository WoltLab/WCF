<?php

namespace wcf\acp\form;

use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\view\user\profile\AdminUserProfileHeaderView;
use wcf\system\WCF;

/**
 * Shows the user edit form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserEditForm extends UserAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canEditUser'];

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters(): void
    {
        $this->formObject = Helper::fetchObjectFromQueryParameter(User::class);
        if (!$this->formObject->canEdit()) {
            throw new PermissionDeniedException();
        }

        parent::readParameters();
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        // Use the saved user, the header would show outdated data otherwise.
        $user = $this->object ?? $this->formObject;

        WCF::getTPL()->assign([
            'userID' => $user->userID,
            'userProfileHeaderView' => new AdminUserProfileHeaderView(new UserProfile($user)),
        ]);
    }
}
