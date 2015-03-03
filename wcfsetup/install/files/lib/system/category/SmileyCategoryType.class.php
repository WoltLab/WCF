<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;
use wcf\system\cache\builder\SmileyCacheBuilder;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category implementation for smilies.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category	Community Framework
 */
class SmileyCategoryType extends AbstractCategoryType {
	/**
	 * @see	\wcf\system\category\AbstractCategoryType::$langVarPrefix
	 */
	protected $langVarPrefix = 'wcf.acp.smiley.category';
	
	/**
	 * @see	\wcf\system\category\AbstractCategoryType::$forceDescription
	 */
	protected $hasDescription = false;
	
	/**
	 * @see	\wcf\system\category\AbstractCategoryType::$maximumNestingLevel
	 */
	protected $maximumNestingLevel = 0;
	
	/**
	 * @see	\wcf\system\category\ICategoryType::afterDeletion()
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		SmileyCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * @see	\wcf\system\category\ICategoryType::canAddCategory()
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @see	\wcf\system\category\ICategoryType::canDeleteCategory()
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @see	\wcf\system\category\ICategoryType::canEditCategory()
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley');
	}
}
