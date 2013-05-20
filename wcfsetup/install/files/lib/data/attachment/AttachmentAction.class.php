<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes attachment-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.attachment
 * @subpackage	data.attachment
 * @category	Community Framework
 */
class AttachmentAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('delete', 'upload');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\attachment\AttachmentEditor';
	
	/**
	 * current attachment object, used to communicate with event listeners
	 * @var	wcf\data\attachment\Attachment
	 */
	public $eventAttachment = null;
	
	/**
	 * current data, used to communicate with event listeners.
	 * @var	array
	 */
	public $eventData = array();
	
	/**
	 * Validates the delete action.
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->objects as $attachment) {
			if ($attachment->tmpHash) {
				if ($attachment->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
			}
			else if (!$attachment->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Validates the upload action.
	 */
	public function validateUpload() {
		// IE<10 fallback
		if (isset($_POST['isFallback'])) {
			$this->parameters['objectType'] = (isset($_POST['objectType'])) ? $_POST['objectType'] : '';
			$this->parameters['objectID'] = (isset($_POST['objectID'])) ? $_POST['objectID'] : 0;
			$this->parameters['parentObjectID'] = (isset($_POST['parentObjectID'])) ? $_POST['parentObjectID'] : 0;
			$this->parameters['tmpHash'] = (isset($_POST['tmpHash'])) ? $_POST['tmpHash'] : '';
		}
		
		// read variables
		$this->readString('objectType');
		$this->readInteger('objectID', true);
		$this->readInteger('parentObjectID', true);
		$this->readString('tmpHash');
		
		// validate object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['objectType']);
		if ($objectType === null) {
			throw new UserInputException('objectType');
		}
		
		// get processor
		$processor = $objectType->getProcessor();
		
		// check upload permissions
		if (!$processor->canUpload((!empty($this->parameters['objectID']) ? intval($this->parameters['objectID']) : 0), (!empty($this->parameters['parentObjectID']) ? intval($this->parameters['parentObjectID']) : 0))) {
			throw new PermissionDeniedException();
		}
		
		// check max count of uploads
		$handler = new AttachmentHandler($this->parameters['objectType'], intval($this->parameters['objectID']), $this->parameters['tmpHash']);
		if ($handler->count() + count($this->parameters['__files']->getFiles()) > $processor->getMaxCount()) {
			throw new UserInputException('files', 'exceededQuota', array(
				'current' => $handler->count(),
				'quota' => $processor->getMaxCount()
			));
		}
		
		// check max filesize, allowed file extensions etc.
		$this->parameters['__files']->validateFiles(new DefaultUploadFileValidationStrategy($processor->getMaxSize(), $processor->getAllowedExtensions()));
	}
	
	/**
	 * Handles uploaded attachments.
	 */
	public function upload() {
		// get object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['objectType']);
		
		// save files
		$thumbnails = $attachments = $failedUploads = array();
		$files = $this->parameters['__files']->getFiles();
		foreach ($files as $file) {
			if ($file->getValidationErrorType()) {
				$failedUploads[] = $file;
				continue;
			}
			
			$data = array(
				'objectTypeID' => $objectType->objectTypeID,
				'objectID' => intval($this->parameters['objectID']),
				'userID' => (WCF::getUser()->userID ?: null),
				'tmpHash' => (!$this->parameters['objectID'] ? $this->parameters['tmpHash'] : ''),
				'filename' => $file->getFilename(),
				'filesize' => $file->getFilesize(),
				'fileType' => $file->getMimeType(),
				'fileHash' => sha1_file($file->getLocation()),
				'uploadTime' => TIME_NOW	
			);
			
			// get image data
			if (($imageData = $file->getImageData()) !== null) {
				$data['isImage'] = 1;
				$data['width'] = $imageData['width'];
				$data['height'] = $imageData['height'];
				$data['fileType'] = $imageData['mimeType'];
			}
			
			// create attachment
			$attachment = AttachmentEditor::create($data);
			
			// check attachment directory
			// and create subdirectory if necessary
			$dir = dirname($attachment->getLocation());
			if (!@file_exists($dir)) {
				@mkdir($dir, 0777);
			}
			
			// move uploaded file
			if (@move_uploaded_file($file->getLocation(), $attachment->getLocation())) {
				if ($attachment->isImage) {
					$thumbnails[] = $attachment;
				}
				else {
					// check whether we can create thumbnails for this file
					$this->eventAttachment = $attachment;
					$this->eventData = array('hasThumbnail' => false);
					EventHandler::getInstance()->fireAction($this, 'checkThumbnail');
					if ($this->eventData['hasThumbnail']) $thumbnails[] = $attachment;
				}
				$attachments[] = $attachment;
			}
			else {
				// moving failed; delete attachment
				$editor = new AttachmentEditor($attachment);
				$editor->delete();
			}
		}
		
		// generate thumbnails
		if (ATTACHMENT_ENABLE_THUMBNAILS) {
			if (!empty($thumbnails)) {
				$action = new AttachmentAction($thumbnails, 'generateThumbnails');
				$action->executeAction();
			}
		}
		
		// return result
		$result = array('attachments' => array(), 'errors' => array());
		if (!empty($attachments)) {
			// get attachment ids
			$attachmentIDs = array();
			foreach ($attachments as $attachment) $attachmentIDs[] = $attachment->attachmentID;
			
			// get attachments from database (check thumbnail status)
			$attachmentList = new AttachmentList();
			$attachmentList->getConditionBuilder()->add('attachment.attachmentID IN (?)', array($attachmentIDs));
			$attachmentList->readObjects();
			
			foreach ($attachmentList as $attachment) {
				$result['attachments'][$attachment->filename] = array(
					'filename' => $attachment->filename,
					'filesize' => $attachment->filesize,
					'formattedFilesize' => FileUtil::formatFilesize($attachment->filesize),
					'isImage' => $attachment->isImage,
					'attachmentID' => $attachment->attachmentID,
					'tinyURL' => ($attachment->tinyThumbnailType ? LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment), 'tiny=1') : ''),
					'thumbnailURL' => ($attachment->thumbnailType ? LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment), 'thumbnail=1') : ''),
					'url' => LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment))
				);
			}
		}
		
		foreach ($failedUploads as $failedUpload) {
			$result['errors'][$failedUpload->getFilename()] = array(
				'filename' => $failedUpload->getFilename(),
				'filesize' => $failedUpload->getFilesize(),
				'errorType' => $failedUpload->getValidationErrorType()
			);
		}
		
		return $result;
	}
	
	/**
	 * Generates thumbnails.
	 */
	public function generateThumbnails() {
		if (!empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $attachment) {
			if (!$attachment->isImage) {
				// create thumbnails for every file that isn't an image
				$this->eventAttachment = $attachment;
				$this->eventData = array();
				
				EventHandler::getInstance()->fireAction($this, 'generateThumbnail');
				
				if (!empty($this->eventData)) {
					$attachment->update($this->eventData);
				}
				
				continue;
			}
			
			if ($attachment->width <= 144 && $attachment->height < 144) {
				continue; // image smaller than thumbnail size; skip
			}
			
			$adapter = ImageHandler::getInstance()->getAdapter();
			$adapter->loadFile($attachment->getLocation());
			$updateData = array();
			
			// create tiny thumbnail
			$tinyThumbnailLocation = $attachment->getTinyThumbnailLocation();
			$thumbnail = $adapter->createThumbnail(144, 144, false);
			$adapter->writeImage($thumbnail, $tinyThumbnailLocation);
			if (file_exists($tinyThumbnailLocation) && ($imageData = @getImageSize($tinyThumbnailLocation)) !== false) {
				$updateData['tinyThumbnailType'] = $imageData['mime'];
				$updateData['tinyThumbnailSize'] = @filesize($tinyThumbnailLocation);
				$updateData['tinyThumbnailWidth'] = $imageData[0];
				$updateData['tinyThumbnailHeight'] = $imageData[1];
			}
			
			// create standard thumbnail
			if ($attachment->width > ATTACHMENT_THUMBNAIL_WIDTH || $attachment->height > ATTACHMENT_THUMBNAIL_HEIGHT) {
				$thumbnailLocation = $attachment->getThumbnailLocation();
				$thumbnail = $adapter->createThumbnail(ATTACHMENT_THUMBNAIL_WIDTH, ATTACHMENT_THUMBNAIL_HEIGHT, false);
				$adapter->writeImage($thumbnail, $thumbnailLocation);
				if (file_exists($thumbnailLocation) && ($imageData = @getImageSize($thumbnailLocation)) !== false) {
					$updateData['thumbnailType'] = $imageData['mime'];
					$updateData['thumbnailSize'] = @filesize($thumbnailLocation);
					$updateData['thumbnailWidth'] = $imageData[0];
					$updateData['thumbnailHeight'] = $imageData[1];
				}
			}
			
			if (!empty($updateData)) {
				$attachment->update($updateData);
			}
		}
	}
}
