<?php
namespace wcf\data\comment;

/**
 * Represents a list of decorated comment objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class ViewableCommentList extends CommentList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\ViewableComment';
	
	/**
	 * Creates a new ViewableCommentList object.
	 */
	public function __construct() {
		parent::__construct();
		
		// get avatars
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_avatar.*, user_table.*";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = comment.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
	}
}
