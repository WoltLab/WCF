<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;
use wcf\system\cache\builder\SmileyCacheBuilder;
use wcf\system\WCF;

/**
 * Category implementation for smilies.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 */
class SmileyCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.acp.smiley.category';
	
	/**
	 * @inheritDoc
	 */
	protected $hasDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 0;
	
	/**
	 * @inheritDoc
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		SmileyCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley');
	}
}
