<?php
namespace wcf\data\language;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes language-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category	Community Framework
 */
class LanguageAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\LanguageEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.language.canAddLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.language.canDeleteLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.language.canEditLanguage');
	
	/**
	 * language editor object
	 * @var	wcf\data\language\LanguageEditor
	 */
	protected $languageEditor = null;
	
	/**
	 * Validates permission to set a language as default.
	 */
	public function validateSetAsDefault() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		$this->languageEditor = $this->getSingleObject();
	}
	
	/**
	 * Sets language as default
	 */
	public function setAsDefault() {
		$this->languageEditor->setAsDefault();
	}
}
