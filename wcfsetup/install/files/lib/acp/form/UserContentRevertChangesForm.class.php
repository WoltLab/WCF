<?php

namespace wcf\acp\form;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\edit\EditHistoryManager;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the user content revert changes form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserContentRevertChangesForm extends AbstractForm
{
    use TMultipleUsersForm;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_EDIT_HISTORY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.canBulkRevertContentChanges'];

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
     * timeframe to consider
     * @var int
     */
    public $timeframe = 7;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->users = $this->readUsersFromRequest();
        foreach ($this->users as $user) {
            if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                throw new PermissionDeniedException();
            }
        }
        $this->userIDs = \array_keys($this->users);
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['timeframe'])) {
            $this->timeframe = \intval($_POST['timeframe']);
        }
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        if ($this->timeframe < 1) {
            throw new UserInputException('timeframe');
        }
    }

    #[\Override]
    public function save()
    {
        parent::save();

        EditHistoryManager::getInstance()->bulkRevert($this->userIDs, $this->timeframe * 86400);

        // reset clipboard
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
            'timeframe' => $this->timeframe,
        ]);
    }
}
