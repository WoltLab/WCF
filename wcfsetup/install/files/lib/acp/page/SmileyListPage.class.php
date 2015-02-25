<?php
namespace wcf\acp\page;
use wcf\data\category\Category;
use wcf\data\smiley\SmileyCache;
use wcf\page\MultipleLinkPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Lists the available smilies.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class SmileyListPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.smiley.canManageSmiley');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_SMILEY');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\smiley\SmileyList';
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'smileyList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sqlOrderBy
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
	 * @var	array<\wcf\data\category\Category>
	 */
	public $categories = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'category' => $this->category,
			'categories' => $this->categories,
			'smileyCount' => count(SmileyCache::getInstance()->getSmilies())
		));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->categoryID) {
			$this->objectList->getConditionBuilder()->add('categoryID = ?', array($this->categoryID));
		}
		else {
			$this->objectList->getConditionBuilder()->add('categoryID IS NULL', array());
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->categories = SmileyCache::getInstance()->getCategories();
	}
}
