<?php
namespace wcf\system\category;
use wcf\system\WCF;

/**
 * Category implementation for media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 * @since	3.1
 */
class MediaCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.media.category';
	
	/**
	 * @inheritDoc
	 */
	protected $hasDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 2;
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return WCF::getSession()->getPermission('admin.content.cms.canManageMedia');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return WCF::getSession()->getPermission('admin.content.cms.canManageMedia');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.content.cms.canManageMedia');
	}
}
