<?php
namespace wcf\data\user\online;
use wcf\data\option\OptionAction;
use wcf\data\session\SessionList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a list of currently online users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.online
 * @category	Community Framework
 */
class UsersOnlineList extends SessionList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_table.username';
	
	/**
	 * users online stats
	 * @var	array
	 */
	public $stats = array(
		'total' => 0,
		'invisible' => 0,
		'members' => 0,
		'guests' => 0
	);
	
	/**
	 * users online markings
	 * @var	array
	 */
	public $usersOnlineMarkings = null;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects .= "user_avatar.*, user_option_value.*, user_group.userOnlineMarking, user_table.*";
		
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = session.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_group user_group ON (user_group.groupID = user_table.userOnlineGroupID)";
		
		$this->getConditionBuilder()->add('session.lastActivityTime > ?', array(TIME_NOW - USER_ONLINE_TIMEOUT));
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		$objects = $this->objects;
		$this->indexToObject = $this->objects = array();
		
		foreach ($objects as $object) {
			$object = new UserOnline(new User(null, null, $object));
			if (!$object->userID || self::isVisible($object->userID, $object->canViewOnlineStatus)) {
				$this->objects[$object->sessionID] = $object;
				$this->indexToObject[] = $object->sessionID;
			}
		}
		$this->objectIDs = $this->indexToObject;
		$this->rewind();
	}
	
	/**
	 * Gets users online stats.
	 */
	public function readStats() {
		$conditionBuilder = clone $this->getConditionBuilder();
		$conditionBuilder->add('session.spiderID IS NULL');
		
		$sql = "SELECT		user_option_value.userOption".User::getUserOptionID('canViewOnlineStatus')." AS canViewOnlineStatus, session.userID
			FROM		wcf".WCF_N."_session session
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = session.userID)
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$this->stats['total']++;
			if ($row['userID']) {
				$this->stats['members']++;
				
				if ($row['canViewOnlineStatus'] && !self::isVisible($row['userID'], $row['canViewOnlineStatus'])) {
					$this->stats['invisible']++;
				}
			}
			else {
				$this->stats['guests']++;
			}
		}
	}
	
	/**
	 * Returns a list of the users online markings.
	 * 
	 * @return	array
	 */
	public function getUsersOnlineMarkings() {
		if ($this->usersOnlineMarkings === null) {
			$this->usersOnlineMarkings = $priorities = array();
			
			// get groups
			foreach (UserGroup::getGroupsByType() as $group) {
				if ($group->userOnlineMarking != '%s') {
					$priorities[] = $group->priority;
					$this->usersOnlineMarkings[] = sprintf($group->userOnlineMarking, StringUtil::encodeHTML(WCF::getLanguage()->get($group->groupName)));
				}
			}
			
			// sort list
			array_multisort($priorities, SORT_DESC, $this->usersOnlineMarkings);
		}
		
		return $this->usersOnlineMarkings;
	}
	
	/**
	 * Checks the users online record.
	 */
	public function checkRecord() {
		$usersOnlineTotal = (USERS_ONLINE_RECORD_NO_GUESTS ? $this->stats['members'] : $this->stats['total']);
		if ($usersOnlineTotal > USERS_ONLINE_RECORD) {
			// save new record
			$optionAction = new OptionAction(array(), 'import', array('data' => array(
				'users_online_record' => $usersOnlineTotal,
				'users_online_record_time' => TIME_NOW
			)));
			$optionAction->executeAction();
		}
	}
	
	/**
	 * Checks the 'canViewOnlineStatus' setting.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$canViewOnlineStatus
	 * @return	boolean
	 */
	public static function isVisible($userID, $canViewOnlineStatus) {
		if (WCF::getSession()->getPermission('admin.user.canViewInvisible') || $userID == WCF::getUser()->userID) return true;
		
		switch ($canViewOnlineStatus) {
			case 0: // everyone
				return true;
			case 1: // registered
				if (WCF::getUser()->userID) return true;
				break;
			case 2: // following
				if (WCF::getUserProfileHandler()->isFollower($userID)) return true;
				break;
		}
		
		return false;
	}
}
