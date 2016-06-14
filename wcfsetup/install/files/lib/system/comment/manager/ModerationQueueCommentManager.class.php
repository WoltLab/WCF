<?php
namespace wcf\system\comment\manager;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Moderation queue comment manager implementation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
 */
class ModerationQueueCommentManager extends AbstractCommentManager {
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		$entry = new ModerationQueue($objectID);
		return $entry->canEdit();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		$entry = new ViewableModerationQueue(new ModerationQueue($objectID));
		return $entry->getLink();
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
	public function updateCounter($objectID, $value) {
		$entry = new ModerationQueue($objectID);
		$editor = new ModerationQueueEditor($entry);
		$editor->updateCounters([
			'comments' => $value
		]);
		$editor->update([
			'lastChangeTime' => TIME_NOW
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAdd($objectID) {
		if (!$this->isAccessible($objectID, true)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function canEdit($isOwner) {
		return $isOwner;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function canDelete($isOwner) {
		return $isOwner;
	}
	
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
