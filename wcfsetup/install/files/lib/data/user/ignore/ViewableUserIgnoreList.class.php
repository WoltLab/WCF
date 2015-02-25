<?php
namespace wcf\data\user\ignore;

/**
 * Represents a list of ignored users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.ignore
 * @category	Community Framework
 */
class ViewableUserIgnoreList extends UserIgnoreList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\ignore\UserIgnore';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\user\UserProfile';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$objectClassName
	 */
	public $objectClassName = 'wcf\data\user\User';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$useQualifiedShorthand
	 */
	public $useQualifiedShorthand = false;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_ignore.ignoreID";
		$this->sqlSelects .= ", user_option_value.*";
		$this->sqlSelects .= ", user_avatar.*";
		
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = user_ignore.ignoreUserID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		
		if (MODULE_USER_RANK) {
			$this->sqlSelects .= ",user_rank.*";
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_rank user_rank ON (user_rank.rankID = user_table.rankID)";
		}
		
		$this->sqlSelects .= ", user_table.*";
	}
}
