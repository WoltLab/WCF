<?php
namespace wcf\data;

/**
 * Default interface for action classes providing grouped user lists.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IGroupedUserListAction {
	/**
	 * Validates parameters to return a parsed list of users.
	 */
	public function validateGetGroupedUserList();
	
	/**
	 * Returns a parsed list of users.
	 * 
	 * @return	array
	 */
	public function getGroupedUserList();
}
