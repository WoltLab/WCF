<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of tags.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class TagListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.tag.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.tag.canManageTag');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TAGGING');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'name';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('tagID', 'languageID', 'name', 'usageCount');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\tag\TagList';
	
	/**
	 * search-query
	 * @var	string
	 */
	public $search = '';
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'search' => $this->search
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['search'])) $this->search = StringUtil::trim($_REQUEST['search']);
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_tag_to_object t2o WHERE t2o.tagID = tag.tagID) AS usageCount";
		$this->objectList->sqlSelects .= ", language.languageName, language.languageCode";
		$this->objectList->sqlSelects .= ", synonym.name AS synonymName";
		
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_language language ON tag.languageID = language.languageID";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_tag synonym ON tag.synonymFor = synonym.tagID";
		
		if ($this->search !== '') {
			$this->objectList->getConditionBuilder()->add('tag.name LIKE ?', array($this->search.'%'));
		}
	}
}
