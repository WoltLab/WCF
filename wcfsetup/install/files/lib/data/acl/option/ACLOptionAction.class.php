<?php
namespace wcf\data\acl\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\acl\ACLHandler;

/**
 * Executes acl option-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option
 * @category	Community Framework
 */
class ACLOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\acl\option\ACLOptionEditor';
	
	/**
	 * Validates parameters for ACL options.
	 */
	public function validateLoadAll() {
		if (!isset($this->parameters['data']['objectTypeID'])) {
			throw new UserInputException('objectTypeID');
		}
	}
	
	/**
	 * Returns a set of permissions and their values if applicable.
	 * 
	 * @return	array
	 */
	public function loadAll() {
		$objectIDs = (isset($this->parameters['data']['objectID'])) ? array($this->parameters['data']['objectID']) : array();
		$permissions = ACLHandler::getInstance()->getPermissions($this->parameters['data']['objectTypeID'], $objectIDs, null, true, true);
		
		return $permissions;
	}
}
