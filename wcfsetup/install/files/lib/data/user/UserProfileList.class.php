<?php
namespace wcf\data\user;

/**
 * Represents a list of user profiles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserProfileList extends UserList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_table.username';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\user\UserProfile';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_avatar.*";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		
		if (MODULE_USER_RANK) {
			$this->sqlSelects .= ",user_rank.*";
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_rank user_rank ON (user_rank.rankID = user_table.rankID)";
		}
		
		// get current location
		$this->sqlSelects .= ", session.controller, session.objectID AS locationObjectID, session.lastActivityTime AS sessionLastActivityTime";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_session session ON (session.userID = user_table.userID)";
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		if ($this->objectIDs === null) {
			$this->readObjectIDs();
		}
		
		parent::readObjects();
	}
}
