<?php
namespace wcf\acp\page;
use wcf\data\category\Category;
use wcf\data\smiley\category\SmileyCategory;
use wcf\data\smiley\SmileyCache;
use wcf\data\smiley\SmileyList;
use wcf\page\MultipleLinkPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Lists the available smilies.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	SmileyList	$objectList
 */
class SmileyListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.smiley.canManageSmiley'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_SMILEY'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = SmileyList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'smileyList';
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'showOrder ASC, smileyID ASC';
	
	/**
	 * category id
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * active category
	 * @var	\wcf\data\category\Category
	 */
	public $category = null;
	
	/**
	 * available categories
	 * @var	SmileyCategory[]
	 */
	public $categories = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			$this->categoryID = intval($_REQUEST['id']);
			$this->category = new Category($this->categoryID);
			if (!$this->category->categoryID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'category' => $this->category,
			'categories' => $this->categories,
			'smileyCount' => count(SmileyCache::getInstance()->getSmilies())
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->categoryID) {
			$this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
		}
		else {
			$this->objectList->getConditionBuilder()->add('categoryID IS NULL', []);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->categories = SmileyCache::getInstance()->getCategories();
	}
}
