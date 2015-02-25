<?php
namespace wcf\system\importer;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\SystemException;

/**
 * Imports attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractAttachmentImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\attachment\Attachment';
	
	/**
	 * object type id for attachments
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// check file location
		if (!@file_exists($additionalData['fileLocation'])) return 0;
		
		// get file hash
		if (empty($data['fileHash'])) $data['fileHash'] = sha1_file($additionalData['fileLocation']);
		
		// get image size
		if (!empty($data['isImage'])) {
			$imageData = @getimagesize($additionalData['fileLocation']);
			if ($imageData !== false) {
				$data['width'] = $imageData[0];
				$data['height'] = $imageData[1];
			}
		}
		
		// get user id
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		
		// check existing attachment id
		if (is_numeric($oldID)) {
			$attachment = new Attachment($oldID);
			if (!$attachment->attachmentID) $data['attachmentID'] = $oldID;
		}
		
		// save attachment
		$attachment = AttachmentEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		
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
