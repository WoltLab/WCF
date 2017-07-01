<?php
namespace wcf\system\category;
use wcf\system\WCF;

/**
 * Trophy category type.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 * @since	3.1
 */
class TrophyCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.trophy.category';
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 0;
	
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
		return WCF::getSession()->getPermission('admin.trophy.canManageTrophy');
	}
}
