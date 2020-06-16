<?php
namespace wcf\system\importer;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Imports attachments.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractAttachmentImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Attachment::class;
	
	/**
	 * object type id for attachments
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		// check file location
		if (!is_readable($additionalData['fileLocation'])) return 0;
		
		// Extract metadata from the file ourselves, because the
		// information pulled from the source database might not
		// be reliable.
		$data['fileHash'] = sha1_file($additionalData['fileLocation']);
		$data['filesize'] = filesize($additionalData['fileLocation']);
		$data['fileType'] = FileUtil::getMimeType($additionalData['fileLocation']);
		
		$imageData = @getimagesize($additionalData['fileLocation']);
		if ($imageData !== false) {
			$data['isImage'] = 1;
			$data['width'] = $imageData[0];
			$data['height'] = $imageData[1];
		}
		else {
			$data['isImage'] = 0;
			$data['width'] = 0;
			$data['height'] = 0;
		}
		
		// get user id
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		// check existing attachment id
		if (ctype_digit((string)$oldID)) {
			$attachment = new Attachment($oldID);
			if (!$attachment->attachmentID) $data['attachmentID'] = $oldID;
		}
		
		// set default last download time
		if (empty($data['lastDownloadTime']) && !empty($data['downloads'])) {
			$data['lastDownloadTime'] = TIME_NOW;
		}
		
		// save attachment
		$attachment = AttachmentEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
		
		// check attachment directory
		// and create subdirectory if necessary
		$dir = dirname($attachment->getLocation());
		if (!@file_exists($dir)) {
			@mkdir($dir, 0777);
		}
		
		// copy file
		try {
			if (!copy($additionalData['fileLocation'], $attachment->getLocation())) {
				throw new SystemException();
			}
			
			return $attachment->attachmentID;
		}
		catch (SystemException $e) {
			// copy failed; delete attachment
			$editor = new AttachmentEditor($attachment);
			$editor->delete();
		}
		
		return 0;
	}
	
	/**
	 * Replaces old attachment BBCodes with BBCodes with the new attachment id.
	 * 
	 * @param	string		$message
	 * @param	integer		$oldID
	 * @param	integer		$newID
	 * @return	string|boolean
	 */
	protected function fixEmbeddedAttachments($message, $oldID, $newID) {
		if (mb_strripos($message, '[attach]'.$oldID.'[/attach]') !== false || mb_strripos($message, '[attach='.$oldID.']') !== false || mb_strripos($message, '[attach='.$oldID.',') !== false) {
			$message = str_ireplace('[attach]'.$oldID.'[/attach]', '[attach]'.$newID.'[/attach]', $message);
			$message = str_ireplace('[attach='.$oldID.']', '[attach='.$newID.']', $message);
			$message = str_ireplace('[attach='.$oldID.',', '[attach='.$newID.',', $message);
			
			return $message;
		}
		
		return false;
	}
}
