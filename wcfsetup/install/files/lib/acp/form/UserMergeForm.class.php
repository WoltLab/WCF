<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Shows the user merge form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserMergeForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditUser'];
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	
	/**
	 * ids of the relevant users
	 * @var	integer[]
	 */
	public $userIDs = [];
	
	/**
	 * relevant users
	 * @var	User[]
	 */
	public $users = [];
	
	/**
	 * destination user id
	 * @var	integer
	 */
	public $destinationUserID = 0;
	
	/**
	 * ids of merge users (without destination user)
	 * @var	integer[]
	 */
	public $mergedUserIDs = [];
	
	/**
	 * id of the user clipboard item object type
	 * @var	integer
	 */
	protected $objectTypeID = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get object type id
		$this->objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
		// get user
		$this->users = ClipboardHandler::getInstance()->getMarkedItems($this->objectTypeID);
		if (empty($this->users) || count($this->users) < 2) {
			throw new IllegalLinkException();
		}
		$this->userIDs = array_keys($this->users);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['destinationUserID'])) $this->destinationUserID = intval($_POST['destinationUserID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (!isset($this->users[$this->destinationUserID])) {
			throw new UserInputException('destinationUserID');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		foreach ($this->userIDs as $userID) {
			if ($userID != $this->destinationUserID) $this->mergedUserIDs[] = $userID;
		}
		
		parent::save();
		
		// poll_option_vote
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_poll_option_vote
			SET		userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// comment
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_comment
			SET	userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// comment_response
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_comment_response
			SET	userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// profile comments
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.user.profileComment');
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [$objectType->objectTypeID]);
		$conditions->add("objectID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_comment
			SET	objectID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// like (userID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_like
			SET		userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		// like (objectUserID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectUserID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_like
			SET	objectUserID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// like_object
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectUserID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_like_object
			SET	objectUserID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// user_follow (userID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$conditions->add("followUserID <> ?", [$this->destinationUserID]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_follow
			SET		userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		// user_follow (followUserID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("followUserID IN (?)", [$this->mergedUserIDs]);
		$conditions->add("userID <> ?", [$this->destinationUserID]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_follow
			SET		followUserID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// user_ignore (userID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$conditions->add("ignoreUserID <> ?", [$this->destinationUserID]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_ignore
			SET		userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		// user_ignore (ignoreUserID)
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("ignoreUserID IN (?)", [$this->mergedUserIDs]);
		$conditions->add("userID <> ?", [$this->destinationUserID]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_ignore
			SET		ignoreUserID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// user_object_watch
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_object_watch
			SET		userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// user_activity_event
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_user_activity_event
			SET	userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// attachments
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// modification_log
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->mergedUserIDs]);
		$sql = "UPDATE	wcf".WCF_N."_modification_log
			SET	userID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge([$this->destinationUserID], $conditions->getParameters()));
		
		// delete merged users
		$action = new UserAction($this->mergedUserIDs, 'delete');
		$action->executeAction();
		
		// reset clipboard
		ClipboardHandler::getInstance()->removeItems($this->objectTypeID);
		SessionHandler::resetSessions($this->userIDs);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('message', 'wcf.global.success');
		WCF::getTPL()->display('success');
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'destinationUserID' => $this->destinationUserID
		]);
	}
}
