<?php
namespace wcf\data\smiley;
use wcf\data\category\Category;
use wcf\data\smiley\category\SmileyCategory;
use wcf\system\cache\builder\SmileyCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the smiley cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Smiley
 */
class SmileyCache extends SingletonFactory {
	/**
	 * cached smilies
	 * @var	Smiley[][]
	 */
	protected $cachedSmilies = [];
	
	/**
	 * cached smilies with smiley code as key
	 * @var	Smiley[]
	 */
	protected $cachedSmileyByCode = [];
	
	/**
	 * cached smiley categories
	 * @var	SmileyCategory[]
	 */
	protected $cachedCategories = [];
	
	/**
	 * enabled smiley categories with at least one smiley
	 * @var	SmileyCategory[]
	 */
	protected $visibleCategories = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get smiley cache
		$this->cachedSmilies = SmileyCacheBuilder::getInstance()->getData([], 'smilies');
		$this->cachedSmileyByCode = SmileyCacheBuilder::getInstance()->getData([], 'codes');
		$smileyCategories = CategoryHandler::getInstance()->getCategories('com.woltlab.wcf.bbcode.smiley');
		
		$this->cachedCategories[null] = new SmileyCategory(new Category(null, [
			'categoryID' => null,
			'parentCategoryID' => 0,
			'title' => 'wcf.acp.smiley.categoryID.default',
			'description' => '',
			'showOrder' => -1,
			'isDisabled' => 0
		]));
		
		foreach ($smileyCategories as $key => $smileyCategory) {
			$this->cachedCategories[$key] = new SmileyCategory($smileyCategory);
		}
	}
	
	/**
	 * Returns all smilies.
	 * 
	 * @return	Smiley[][]
	 */
	public function getSmilies() {
		return $this->cachedSmilies;
	}
	
	/**
	 * Returns the smiley with the given smiley code or `null` if no such smiley exists.
	 * 
	 * @param	string		$code
	 * @return	Smiley|null
	 */
	public function getSmileyByCode($code) {
		if (isset($this->cachedSmileyByCode[$code])) {
			return $this->cachedSmileyByCode[$code];
		}
		
		return null;
	}
	
	/**
	 * Returns all smiley categories.
	 * 
	 * @return	SmileyCategory[]
	 */
	public function getCategories() {
		return $this->cachedCategories;
	}
	
	/**
	 * Returns all enabled smiley categories with at least one smiley.
	 * 
	 * @return	SmileyCategory[]
	 */
	public function getVisibleCategories() {
		if ($this->visibleCategories === null) {
			$this->visibleCategories = [];
			
			foreach ($this->cachedCategories as $key => $category) {
				if (!$category->isDisabled) {
					$category->loadSmilies();
					
					if (count($category)) {
						$this->visibleCategories[$key] = $category;
					}
				}
			}
		}
		
		return $this->visibleCategories;
	}
	
	/**
	 * Returns all the smilies of a category.
	 * 
	 * @param	integer		$categoryID
	 * @return	array
	 */
	public function getCategorySmilies($categoryID = null) {
		if (isset($this->cachedSmilies[$categoryID])) return $this->cachedSmilies[$categoryID];
		
		return [];
	}
	
	/**
	 * Assigns the smilies and their categories to the template.
	 * 
	 * @since 3.2
	 */
	public function assignVariables() {
		if (!MODULE_SMILEY) {
			return;
		}
		
		$smileyCategories = $this->getVisibleCategories();
		$firstCategory = reset($smileyCategories);
		$defaultSmilies = [];
		if ($firstCategory) {
			$defaultSmilies = $this->getCategorySmilies($firstCategory->categoryID ?: null);
		}
		
		WCF::getTPL()->assign([
			'defaultSmilies' => $defaultSmilies,
			'smileyCategories' => $smileyCategories,
		]);
	}
}
