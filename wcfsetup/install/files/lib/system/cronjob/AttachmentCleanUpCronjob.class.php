<?php
namespace wcf\system\cronjob;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Deletes orphaned attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.attachment
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class AttachmentCleanUpCronjob extends AbstractCronjob {
	/**
	 * @see	wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// delete orphaned attachments
		$attachmentIDs = array();
		$sql = "SELECT	attachmentID
			FROM	wcf".WCF_N."_attachment
			WHERE	objectID = ?
				AND uploadTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			0,
			(TIME_NOW - 86400)
		));
		while ($row = $statement->fetchArray()) {
			$attachmentIDs[] = $row['attachmentID'];
		}
		
		if (!empty($attachmentIDs)) {
			AttachmentEditor::deleteAll($attachmentIDs);
		}
	}
}
