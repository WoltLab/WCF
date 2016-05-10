<?php
namespace wcf\data\user;

/**
 * Represents a list of user profiles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 *
 * @method	UserProfile		current()
 * @method	UserProfile[]		getObjects()
 * @method	UserProfile|null	search($objectID)
 * @property	UserProfile[]		$objects
 */
class UserProfileList extends UserList {
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'user_table.username';
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = UserProfile::class;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readObjects() {
		if ($this->objectIDs === null) {
			$this->readObjectIDs();
		}
		
		parent::readObjects();
	}
}
