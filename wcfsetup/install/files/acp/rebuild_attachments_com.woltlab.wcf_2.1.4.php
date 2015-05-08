<?php
use wcf\data\attachment\AttachmentList;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

/**
 * Checks the filesize of all image attachments since the release of WCF 2.1 due
 * to issues with rotated images whose filesize is incorrect.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
$minimumAttachmentTime = 1425219870; // time of the release note

$attachmentsPerRun = 100;
$rebuildData = WCF::getSession()->getVar('__wcfUpdateRebuildAttachments');
if ($rebuildData === null) {
	$sql = "SELECT	COUNT(*)
		FROM	wcf".WCF_N."_attachment
		WHERE	isImage = ?
			AND uploadTime >= ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute(array(
		1,
		$minimumAttachmentTime
	));
	$count = $statement->fetchColumn();
	
	$rebuildData = array(
		'i' => 0,
		'max' => 0
	);
	
	if ($count) {
		$rebuildData['max'] = ceil($count / $attachmentsPerRun);
	}
}

if ($rebuildData['max']) {
	// get attachment file data
	$attachmentList = new AttachmentList();
	$attachmentList->getConditionBuilder()->add('isImage = ?', array(1));
	$attachmentList->getConditionBuilder()->add('uploadTime >= ?', array($minimumAttachmentTime));
	$attachmentList->sqlOffset = $rebuildData['i'] * $attachmentsPerRun;
	$attachmentList->sqlLimit = $attachmentsPerRun;
	$attachmentList->readObjects();
	
	if (!count($attachmentList)) {
		// all relevant attachments have been processed
		WCF::getSession()->unregister('__wcfUpdateRebuildAttachments');
	}
	else {
		$attachmentUpdates = array();
		foreach ($attachmentList as $attachment) {
			$filesize = filesize($attachment->getLocation());
			if ($filesize != $attachment->filesize) {
				$attachmentUpdates[$attachment->attachmentID] = $filesize;
			}
		}
		
		if (!empty($attachmentUpdates)) {
			$sql = "UPDATE	wcf".WCF_N."_attachment
				SET	filesize = ?
				WHERE	attachmentID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($attachmentUpdates as $attachmentID => $filesize) {
				$statement->execute(array(
					$filesize,
					$attachmentID
				));
			}
			WCF::getDB()->commitTransaction();
		}
		
		// update rebuiled data
		$rebuildData['i']++;
		WCF::getSession()->register('__wcfUpdateRebuildAttachments', $rebuildData);
		
		throw new SplitNodeException();
	}
}
