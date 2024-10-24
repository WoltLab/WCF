<?php

namespace wcf\acp\form;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
        // get user
        $this->users = ClipboardHandler::getInstance()->getMarkedItems($this->objectTypeID);
        if (empty($this->users) || \count($this->users) < 2) {
            throw new IllegalLinkException();
        }
        foreach ($this->users as $user) {
            if (!$user->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
        $this->userIDs = \array_keys($this->users);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['destinationUserID'])) {
            $this->destinationUserID = \intval($_POST['destinationUserID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (!isset($this->users[$this->destinationUserID])) {
            throw new UserInputException('destinationUserID');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        foreach ($this->userIDs as $userID) {
            if ($userID != $this->destinationUserID) {
                $this->mergedUserIDs[] = $userID;
            }
        }

        parent::save();

        // poll_option_vote
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE IGNORE   wcf1_poll_option_vote
                SET             userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // comment
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_comment
                SET     userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // comment_response
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_comment_response
                SET     userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // profile comments
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.comment.commentableContent',
            'com.woltlab.wcf.user.profileComment'
        );
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectType->objectTypeID]);
        $conditions->add("objectID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_comment
                SET     objectID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // like (userID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE IGNORE   wcf1_like
                SET             userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));
        // like (objectUserID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectUserID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_like
                SET     objectUserID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // like_object
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectUserID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_like_object
                SET     objectUserID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // user_follow (userID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $conditions->add("followUserID <> ?", [$this->destinationUserID]);
        $sql = "UPDATE IGNORE   wcf1_user_follow
                SET             userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));
        // user_follow (followUserID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("followUserID IN (?)", [$this->mergedUserIDs]);
        $conditions->add("userID <> ?", [$this->destinationUserID]);
        $sql = "UPDATE IGNORE   wcf1_user_follow
                SET             followUserID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // user_ignore (userID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $conditions->add("ignoreUserID <> ?", [$this->destinationUserID]);
        $sql = "UPDATE IGNORE   wcf1_user_ignore
                SET             userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));
        // user_ignore (ignoreUserID)
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("ignoreUserID IN (?)", [$this->mergedUserIDs]);
        $conditions->add("userID <> ?", [$this->destinationUserID]);
        $sql = "UPDATE IGNORE   wcf1_user_ignore
                SET             ignoreUserID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // user_object_watch
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE IGNORE   wcf1_user_object_watch
                SET             userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // user_activity_event
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_user_activity_event
                SET     userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // attachments
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_attachment
                SET     userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // modification_log
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE  wcf1_modification_log
                SET     userID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // user notifications
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("authorID IN (?)", [$this->mergedUserIDs]);
        $sql = "UPDATE IGNORE   wcf1_user_notification_author
                SET             authorID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUserID], $conditions->getParameters()));

        // delete merged users
        $action = new UserAction($this->mergedUserIDs, 'delete');
        $action->executeAction();

        // reset clipboard
        ClipboardHandler::getInstance()->removeItems($this->objectTypeID);
        UserEditor::resetCache();
        $this->saved();

        // show success message
        WCF::getTPL()->assign('message', 'wcf.global.success');
        WCF::getTPL()->display('success');

        exit;
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
            'destinationUserID' => $this->destinationUserID,
        ]);
    }
}
