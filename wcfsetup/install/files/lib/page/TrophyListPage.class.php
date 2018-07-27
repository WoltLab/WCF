<?php
namespace wcf\page;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\TrophyList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Represents a trophy page.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 *
 * @property	TrophyList	$objectList
 */
class TrophyListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TROPHY'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.trophy.canSeeTrophies'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 30;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TrophyList::class;
	
	/**
	 * selected sort field
	 * @var	string
	 */
	public $sortField = 'trophyID';
	
	/**
	 * selected sort order
	 * @var	string
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * the category id filter
	 * @var int
	 */
	public $categoryID = 0;
	
	/**
	 * The category object filter 
	 * @var TrophyCategory
	 */
	public $category;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		
		$this->category = TrophyCategoryCache::getInstance()->getCategoryByID($this->categoryID);
		
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'category' => $this->category,
			'categoryID' => $this->categoryID,
			'categories' => TrophyCategoryCache::getInstance()->getEnabledCategories()
		]);
		
		if (count($this->objectList) === 0) {
			@header('HTTP/1.1 404 Not Found');
		}
	}
}
