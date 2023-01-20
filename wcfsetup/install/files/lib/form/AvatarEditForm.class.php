<?php

namespace wcf\form;

use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\UserAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Shows the avatar edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AvatarEditForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $templateName = 'avatarEdit';

    /**
     * avatar type
     * @var string
     */
    public $avatarType = 'none';

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['avatarType'])) {
            $this->avatarType = $_POST['avatarType'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (WCF::getUser()->disableAvatar) {
            throw new PermissionDeniedException();
        }

        if ($this->avatarType != 'custom') {
            $this->avatarType = 'none';
        }

        switch ($this->avatarType) {
            case 'custom':
                if (!WCF::getUser()->avatarID) {
                    throw new UserInputException('custom');
                }
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        if ($this->avatarType != 'custom') {
            // delete custom avatar
            if (WCF::getUser()->avatarID) {
                $action = new UserAvatarAction([WCF::getUser()->avatarID], 'delete');
                $action->executeAction();
            }
        }

        // update user
        $data = [];
        if ($this->avatarType === 'none') {
            $data = [
                'avatarID' => null,
            ];
        }
        $this->objectAction = new UserAction([WCF::getUser()], 'update', [
            'data' => \array_merge($this->additionalFields, $data),
        ]);
        $this->objectAction->executeAction();

        // check if the user will be automatically added to new user groups
        // because of the changed avatar
        UserGroupAssignmentHandler::getInstance()->checkUsers([WCF::getUser()->userID]);

        UserProfileHandler::getInstance()->reloadUserProfile();

        $this->saved();
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            if (WCF::getUser()->avatarID) {
                $this->avatarType = 'custom';
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'avatarType' => $this->avatarType,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.avatar');

        parent::show();
    }
}
