<?php
namespace wcf\data\attachment;
use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.attachment
 * @category	Community Framework
 *
 * @method	Attachment	getDecoratedObject()
 * @mixin	Attachment
 */
class AttachmentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Attachment::class;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_attachment
			WHERE		attachmentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->attachmentID]);
		
		$this->deleteFiles();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		// delete files first
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("attachmentID IN (?)", [$objectIDs]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_attachment
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($attachment = $statement->fetchObject(static::$baseClass)) {
			$editor = new AttachmentEditor($attachment);
			$editor->deleteFiles();
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Deletes attachment files.
	 */
	public function deleteFiles() {
		@unlink($this->getLocation());
		if ($this->tinyThumbnailType) {
			@unlink($this->getTinyThumbnailLocation());
		}
		if ($this->thumbnailType) {
			@unlink($this->getThumbnailLocation());
		}
	}
}
