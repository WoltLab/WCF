<?php

namespace wcf\acp\form;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the assign user to group form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserAssignToGroupForm extends AbstractForm
{
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
     * ids of the assigned user groups
     * @var int[]
     */
    public $groupIDs = [];

    /**
     * relevant users
     * @var User[]
     */
    public $users = [];

    /**
     * assigned user groups
     * @var UserGroup[]
     */
    public $groups = [];

    /**
     * id of the user clipboard item object type
     * @var int
     */
    protected $objectTypeID;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get object type id
        $this->objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
        if ($this->objectTypeID === null) {
            throw new SystemException("clipboard item type 'com.woltlab.wcf.user' is unknown.");
        }

        // get user
        $this->users = ClipboardHandler::getInstance()->getMarkedItems($this->objectTypeID);
        if (empty($this->users)) {
            throw new IllegalLinkException();
        }

        $this->userIDs = \array_keys($this->users);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['groupIDs']) && \is_array($_POST['groupIDs'])) {
            $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (empty($this->userIDs)) {
            throw new IllegalLinkException();
        }

        // groups
        foreach ($this->groupIDs as $groupID) {
            $group = new UserGroup($groupID);
            if (!$group->groupID) {
                throw new UserInputException('groupIDs');
            }
            if (!$group->isAccessible()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->userIDs]);

        $sql = "SELECT  userID, groupID
                FROM    wcf1_user_to_group
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $groups = $statement->fetchMap('userID', 'groupID', false);

        foreach ($this->users as $user) {
            if (!UserGroup::isAccessibleGroup($groups[$user->userID])) {
                throw new PermissionDeniedException();
            }

            $groupsIDs = \array_merge($groups[$user->userID], $this->groupIDs);
            $groupsIDs = \array_unique($groupsIDs);

            $action = new UserAction([new UserEditor($user)], 'addToGroups', [
                'groups' => $groupsIDs,
                'addDefaultGroups' => false,
            ]);
            $action->executeAction();
        }

        ClipboardHandler::getInstance()->removeItems($this->objectTypeID);

        $this->saved();

        WCF::getTPL()->assign([
            'groupIDs' => $this->groupIDs,
            'message' => 'wcf.acp.user.assignToGroup.success',
            'users' => $this->users,
        ]);
        WCF::getTPL()->display('success');

        exit;
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->readGroups();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'users' => $this->users,
            'userIDs' => $this->userIDs,
            'groupIDs' => $this->groupIDs,
            'groups' => $this->groups,
        ]);
    }

    /**
     * Get a list of available groups.
     */
    protected function readGroups()
    {
        $this->groups = UserGroup::getSortedAccessibleGroups(
            [],
            [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]
        );
    }
}
