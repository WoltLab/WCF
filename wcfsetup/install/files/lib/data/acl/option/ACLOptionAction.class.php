<?php
namespace wcf\data\acl\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\acl\ACLHandler;

/**
 * Executes acl option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acl\Option
 * 
 * @method	ACLOption		create()
 * @method	ACLOptionEditor[]	getObjects()
 * @method	ACLOptionEditor		getSingleObject()
 */
class ACLOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACLOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['loadAll'];
	
	/**
	 * Validates parameters for ACL options.
	 */
	public function validateLoadAll() {
		$this->readInteger('objectID', true);
		$this->readInteger('objectTypeID');
		$this->readString('categoryName', true);
	}
	
	/**
	 * Returns a set of permissions and their values if applicable.
	 * 
	 * @return	array
	 */
	public function loadAll() {
		$objectIDs = ($this->parameters['objectID']) ? [$this->parameters['objectID']] : [];
		$permissions = ACLHandler::getInstance()->getPermissions($this->parameters['objectTypeID'], $objectIDs, $this->parameters['categoryName'], true);
		
		return $permissions;
	}
}
