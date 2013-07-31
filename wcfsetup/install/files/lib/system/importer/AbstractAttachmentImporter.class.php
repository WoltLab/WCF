<?php
namespace wcf\system\importer;
use wcf\data\attachment\AttachmentAction;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\SystemException;
use wcf\util\StringUtil;

/**
 * Imports attachments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractAttachmentImporter implements IImporter {
	/**
	 * object type id for attachments
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see wcf\system\importer\IImporter::import()
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
		
		// save attachment
		$action = new AttachmentAction(array(), 'create', array(
			'data' => array_merge($data, array('objectTypeID' => $this->objectTypeID))		
		));
		$returnValues = $action->executeAction();
		$attachment = $returnValues['returnValues'];
		
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
		if (StringUtil::indexOfIgnoreCase($message, '[attach]'.$oldID.'[/attach]') !== false || StringUtil::indexOfIgnoreCase($message, '[attach='.$oldID.']') !== false) {
			$message = StringUtil::replaceIgnoreCase('[attach]'.$oldID.'[/attach]', '[attach]'.$newID.'[/attach]', $message);
			$message = StringUtil::replaceIgnoreCase('[attach='.$oldID.']', '[attach='.$newID.']', $message);
		
			return $message;
		}
		
		return false;
	}
}
