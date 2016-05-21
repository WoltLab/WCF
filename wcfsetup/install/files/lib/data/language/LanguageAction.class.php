<?php
namespace wcf\data\language;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes language-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category	Community Framework
 */
class LanguageAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LanguageEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.language.canManageLanguage'];
	
	/**
	 * language editor object
	 * @var	\wcf\data\language\LanguageEditor
	 */
	protected $languageEditor = null;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'setAsDefault', 'update'];
	
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
