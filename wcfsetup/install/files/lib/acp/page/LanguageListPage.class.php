<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all installed languages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class LanguageListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.list';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'languageName';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canManageLanguage');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\language\LanguageList';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('languageID', 'languageCode', 'languageName', 'users', 'variables', 'customVariables');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user user WHERE languageID = language.languageID) AS users,";
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID) AS variables,";
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID AND languageCustomItemValue IS NOT NULL) AS customVariables";
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languages' => $this->objectList->getObjects()
		));
	}
}
