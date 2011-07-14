<?php
namespace wcf\data\language;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes language-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category 	Community Framework
 */
class LanguageAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\LanguageEditor';
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.language.canAddLanguage');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.language.canDeleteLanguage');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.language.canEditLanguage');
	
	/**
	 * Validates permission to set a language as default.
	 */
	public function validateSetAsDefault() {
		try {
			WCF::getSession()->checkPermission($this->permissionsUpdate);
		}
		catch (PermissionDeniedException $e) {
			throw new ValidateActionException('Insufficient permissions');
		}
		
		// read data
		$this->readObjects();
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
	}
	
	/**
	 * Sets language as default
	 */	
	public function setAsDefault() {
		$language = array_shift($this->objects);
		$language->setAsDefault();
	}
}
?>
