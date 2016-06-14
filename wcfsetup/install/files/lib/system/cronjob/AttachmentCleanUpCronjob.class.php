<?php
namespace wcf\system\cronjob;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Deletes orphaned attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class AttachmentCleanUpCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// delete orphaned attachments
		$sql = "SELECT	attachmentID
			FROM	wcf".WCF_N."_attachment
			WHERE	objectID = ?
				AND uploadTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			0,
			(TIME_NOW - 86400)
		]);
		$attachmentIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($attachmentIDs)) {
			AttachmentEditor::deleteAll($attachmentIDs);
		}
	}
}
