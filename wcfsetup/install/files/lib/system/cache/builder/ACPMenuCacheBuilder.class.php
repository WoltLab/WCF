<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\data\acp\menu\item\ACPMenuItemList;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\category\OptionCategoryList;
use wcf\data\option\OptionList;
use wcf\system\request\LinkHandler;

/**
 * Caches the ACP menu items.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACPMenuCacheBuilder implements ICacheBuilder {
	/**
	 * list of option categories which directly contain options
	 * @var	array<string>
	 */
	protected $categoriesWithOptions = array();
	
	/**
	 * list of option categories grouped by the name of their parent category
	 * @var	array<wcf\data\option\category\OptionCategory>
	 */
	protected $categoryStructure = array();
	
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) { 
		$data = array();
		
		// get "real" menu items
		$menuItemList = new ACPMenuItemList();
		$menuItemList->sqlLimit = 0;
		$menuItemList->readObjects();
		foreach ($menuItemList as $menuItem) {
			$data[$menuItem->parentMenuItem][] = $menuItem;
		}
		
		// get menu items for top option categories
		$data['wcf.acp.menu.link.option.category'] = array();
		foreach ($this->getTopOptionCategories() as $optionCategory) {
			$data['wcf.acp.menu.link.option.category'][] = new ACPMenuItem(null, array(
				'menuItem' => 'wcf.acp.option.category.'.$optionCategory->categoryName,
				'parentMenuItem' => 'wcf.acp.menu.link.option.category',
				'menuItemLink' => LinkHandler::getInstance()->getLink('Option', array(
					'application' => 'wcf',
					'id' => $optionCategory->categoryID
				)),
				'permissions' => $optionCategory->permissions,
				'options' => $optionCategory->options
			));
		}
		
		return $data;
	}
	
	/**
	 * Returns the list with top option categories which contain options.
	 * 
	 * @return	array<wcf\data\option\category\OptionCategory>
	 */
	protected function getTopOptionCategories() {
		$optionCategoryList = new OptionCategoryList();
		$optionCategoryList->sqlLimit = 0;
		$optionCategoryList->readObjects();
		$optionCategories = $optionCategoryList->getObjects();
		
		// build category structure
		$this->categoryStructure = array();
		foreach ($optionCategories as $optionCategory) {
			if (!isset($this->categoryStructure[$optionCategory->parentCategoryName])) {
				$this->categoryStructure[$optionCategory->parentCategoryName] = array();
			}
			
			$this->categoryStructure[$optionCategory->parentCategoryName][] = $optionCategory;
		}
		
		$optionList = new OptionList();
		$optionList->sqlLimit = 0;
		$optionList->readObjects();
		
		// collect names of categories which contain options
		foreach ($optionList as $option) {
			if (!isset($this->categoriesWithOptions[$option->categoryName])) {
				$this->categoriesWithOptions[$option->categoryName] = $option->categoryName;
			}
		}
		
		// collect top categories which contain options
		$topCategories = array();
		foreach ($this->categoryStructure[""] as $topCategory) {
			if ($this->containsOptions($topCategory)) {
				$topCategories[$topCategory->categoryID] = $topCategory;
			}
		}
		
		return $topCategories;
	}
	
	/**
	 * Returns true if the given category or one of its child categories contains
	 * options.
	 * 
	 * @return	boolean
	 */
	protected function containsOptions(OptionCategory $topCategory) {
		// check if category directly contains options
		if (isset($this->categoriesWithOptions[$topCategory->categoryName])) {
			return true;
		}
		
		if (!isset($this->categoryStructure[$topCategory->categoryName])) {
			// if category directly contains no options and has no child
			// categories, it contains no options at all
			return false;
		}
		
		// check child categories
		foreach ($this->categoryStructure[$topCategory->categoryName] as $category) {
			if ($this->containsOptions($category)) {
				return true;
			}
		}
		
		return false;
	}
}
