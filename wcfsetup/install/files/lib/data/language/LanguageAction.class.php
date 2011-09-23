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
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\LanguageEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$createPermissions
	 */
	protected $createPermissions = array('admin.language.canAddLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$deletePermissions
	 */
	protected $deletePermissions = array('admin.language.canDeleteLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$updatePermissions
	 */
	protected $updatePermissions = array('admin.language.canEditLanguage');
	
	/**
	 * Validates permission to set a language as default.
	 */
	public function validateSetAsDefault() {
		try {
			WCF::getSession()->checkPermissions($this->updatePermissions);
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
