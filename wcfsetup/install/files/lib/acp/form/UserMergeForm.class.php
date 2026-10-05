<?php

namespace wcf\acp\form;

use wcf\command\user\MergeUsers;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the user merge form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserMergeForm extends AbstractForm
{
    use TMultipleUsersForm;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canEditUser'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.list';

    /**
     * ids of the relevant users
     * @var int[]
     */
    public $userIDs = [];

    /**
     * relevant users
     * @var User[]
     */
    public $users = [];

    /**
     * destination user id
     * @var int
     */
    public $destinationUserID = 0;

    /**
     * ids of merge users (without destination user)
     * @var int[]
     */
    public $mergedUserIDs = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->users = $this->readUsersFromRequest();
        if (\count($this->users) < 2) {
            throw new IllegalLinkException();
        }
        foreach ($this->users as $user) {
            if (!$user->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
        $this->userIDs = \array_keys($this->users);
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['destinationUserID'])) {
            $this->destinationUserID = \intval($_POST['destinationUserID']);
        }
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        if (!isset($this->users[$this->destinationUserID])) {
            throw new UserInputException('destinationUserID');
        }
    }

    #[\Override]
    public function save()
    {
        $mergedUsers = [];
        foreach ($this->users as $userID => $user) {
            if ($userID !== $this->destinationUserID) {
                $mergedUsers[] = $user;
                $this->mergedUserIDs[] = $userID;
            }
        }

        parent::save();

        new MergeUsers($this->users[$this->destinationUserID], $mergedUsers)();

        $this->saved();

        // show success message
        WCF::getTPL()->assign('message', 'wcf.global.success');
        WCF::getTPL()->display('success');

        exit;
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'users' => $this->users,
            'userIDs' => $this->userIDs,
            'destinationUserID' => $this->destinationUserID,
        ]);
    }
}
