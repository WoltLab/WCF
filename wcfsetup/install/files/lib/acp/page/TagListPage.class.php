<?php
namespace wcf\acp\page;
use wcf\data\tag\TagList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of tags.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	TagList		$objectList
 */
class TagListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.tag.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.tag.canManageTag'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TAGGING'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'name';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['tagID', 'languageID', 'name', 'usageCount'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TagList::class;
	
	/**
	 * search-query
	 * @var	string
	 */
	public $search = '';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.tag')),
			'search' => $this->search
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['search'])) $this->search = StringUtil::trim($_REQUEST['search']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_tag_to_object t2o WHERE t2o.tagID = tag.tagID) AS usageCount";
		$this->objectList->sqlSelects .= ", language.languageName, language.languageCode";
		$this->objectList->sqlSelects .= ", synonym.name AS synonymName";
		
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_language language ON tag.languageID = language.languageID";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_tag synonym ON tag.synonymFor = synonym.tagID";
		
		if ($this->search !== '') {
			$this->objectList->getConditionBuilder()->add('tag.name LIKE ?', [$this->search.'%']);
		}
	}
}
