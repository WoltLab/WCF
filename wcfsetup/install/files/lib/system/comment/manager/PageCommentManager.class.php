<?php
namespace wcf\system\comment\manager;
use wcf\system\request\LinkHandler;

/**
 * Page comment manager implementation.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
class PageCommentManager extends AbstractCommentManager {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.pageComment.canAddComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.pageComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.pageComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.pageComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.pageComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// @todo
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getCmsLink($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {}
	
	/**
	 * @inheritDoc
	 */
	public function supportsLike() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsReport() {
		return false;
	}
}
