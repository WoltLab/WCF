<?php
namespace wcf\system\comment\manager;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Moderation queue comment manager implementation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
class ModerationQueueCommentManager extends AbstractCommentManager {
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::isAccessible()
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		$entry = new ModerationQueue($objectID);
		return $entry->canEdit();
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getLink()
	 */
	public function getLink($objectTypeID, $objectID) {
		$entry = new ViewableModerationQueue(new ModerationQueue($objectID));
		return $entry->getLink();
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getTitle()
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		return '';
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::updateCounter()
	 */
	public function updateCounter($objectID, $value) {
		$entry = new ModerationQueue($objectID);
		$editor = new ModerationQueueEditor($entry);
		$editor->updateCounters(array(
			'comments' => $value
		));
		$editor->update(array(
			'lastChangeTime' => TIME_NOW
		));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canAdd()
	 */
	public function canAdd($objectID) {
		if (!$this->isAccessible($objectID, true)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::canEdit()
	 */
	protected function canEdit($isOwner) {
		return $isOwner;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::canDelete()
	 */
	protected function canDelete($isOwner) {
		return $isOwner;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::supportsLike()
	 */
	public function supportsLike() {
		return false;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::supportsReport()
	 */
	public function supportsReport() {
		return false;
	}
}
