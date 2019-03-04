<?php
namespace wcf\system\category;
use wcf\system\WCF;

/**
 * Category type implementation for article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 * @since	3.0
 */
class ArticleCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.article.category';
	
	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 2;
	
	/**
	 * @inheritDoc
	 */
	protected $objectTypes = ['com.woltlab.wcf.acl' => 'com.woltlab.wcf.article.category'];
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.content.article.canManageCategory');
	}
	
	/**
	 * @inheritDoc
	 * @sicne	5.2
	 */
	public function supportsHtmlDescription() {
		return true;
	}
}
