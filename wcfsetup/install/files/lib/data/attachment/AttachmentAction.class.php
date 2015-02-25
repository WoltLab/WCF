<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\ExifUtil;
use wcf\util\FileUtil;

/**
 * Executes attachment-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.attachment
 * @category	Community Framework
 */
class AttachmentAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('delete', 'updatePosition', 'upload');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\attachment\AttachmentEditor';
	
	/**
	 * current attachment object, used to communicate with event listeners
	 * @var	\wcf\data\attachment\Attachment
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
				$data['width'] = $imageData['width'];
				$data['height'] = $imageData['height'];
				$data['fileType'] = $imageData['mimeType'];
				
				if (preg_match('~^image/(gif|jpe?g|png)$~i', $data['fileType'])) {
					$data['isImage'] = 1;
				}
			}
			
			// create attachment
			$attachment = AttachmentEditor::create($data);
			
			// check attachment directory
			// and create subdirectory if necessary
			$dir = dirname($attachment->getLocation());
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir, 0777);
			}
			
			// move uploaded file
			if (@move_uploaded_file($file->getLocation(), $attachment->getLocation())) {
				if ($attachment->isImage) {
					$thumbnails[] = $attachment;
					
					// rotate image based on the exif data
					$neededMemory = $attachment->width * $attachment->height * ($attachment->fileType == 'image/png' ? 4 : 3) * 2.1;
					if (FileUtil::getMemoryLimit() == -1 || FileUtil::getMemoryLimit() > (memory_get_usage() + $neededMemory)) {
						$exifData = ExifUtil::getExifData($attachment->getLocation());
						if (!empty($exifData)) {
							$orientation = ExifUtil::getOrientation($exifData);
							if ($orientation != ExifUtil::ORIENTATION_ORIGINAL) {
								$adapter = ImageHandler::getInstance()->getAdapter();
								$adapter->loadFile($attachment->getLocation());
								
								$newImage = null;
								switch ($orientation) {
									case ExifUtil::ORIENTATION_180_ROTATE:
										$newImage = $adapter->rotate(180);
									break;
									
									case ExifUtil::ORIENTATION_90_ROTATE:
										$newImage = $adapter->rotate(90);
									break;
									
									case ExifUtil::ORIENTATION_270_ROTATE:
										$newImage = $adapter->rotate(270);
									break;
									
									case ExifUtil::ORIENTATION_HORIZONTAL_FLIP:
									case ExifUtil::ORIENTATION_VERTICAL_FLIP:
									case ExifUtil::ORIENTATION_VERTICAL_FLIP_270_ROTATE:
									case ExifUtil::ORIENTATION_HORIZONTAL_FLIP_270_ROTATE:
										// unsupported
									break;
								}
								
								if ($newImage !== null) {
									$adapter->load($newImage, $adapter->getType());
									
									// update width and height of the attachment
									if ($orientation == ExifUtil::ORIENTATION_90_ROTATE || $orientation == ExifUtil::ORIENTATION_270_ROTATE) {
										$attachmentEditor = new AttachmentEditor($attachment);
										$attachmentEditor->update(array(
											'height' => $attachment->width,
											'width' => $attachment->height
										));
									}
								}
								
								$adapter->writeImage($attachment->getLocation());
							}
						}
					}
				}
				else {
					// check whether we can create thumbnails for this file
					$this->eventAttachment = $attachment;
					$this->eventData = array('hasThumbnail' => false);
					EventHandler::getInstance()->fireAction($this, 'checkThumbnail');
					if ($this->eventData['hasThumbnail']) $thumbnails[] = $attachment;
				}
				$attachments[$file->getInternalFileID()] = $attachment;
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
			$attachmentIDs = $attachmentToFileID = array();
			foreach ($attachments as $internalFileID => $attachment) {
				$attachmentIDs[] = $attachment->attachmentID;
				$attachmentToFileID[$attachment->attachmentID] = $internalFileID;
			}
			
			// get attachments from database (check thumbnail status)
			$attachmentList = new AttachmentList();
			$attachmentList->getConditionBuilder()->add('attachment.attachmentID IN (?)', array($attachmentIDs));
			$attachmentList->readObjects();
			
			foreach ($attachmentList as $attachment) {
				$result['attachments'][$attachmentToFileID[$attachment->attachmentID]] = array(
					'filename' => $attachment->filename,
					'filesize' => $attachment->filesize,
					'formattedFilesize' => FileUtil::formatFilesize($attachment->filesize),
					'isImage' => $attachment->isImage,
					'attachmentID' => $attachment->attachmentID,
					'tinyURL' => ($attachment->tinyThumbnailType ? LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment), 'tiny=1') : ''),
					'thumbnailURL' => ($attachment->thumbnailType ? LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment), 'thumbnail=1') : ''),
					'url' => LinkHandler::getInstance()->getLink('Attachment', array('object' => $attachment)),
					'height' => $attachment->height,
					'width' => $attachment->width
				);
			}
		}
		
		foreach ($failedUploads as $failedUpload) {
			$result['errors'][$failedUpload->getInternalFileID()] = array(
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
		if (empty($this->objects)) {
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
			
			// check memory limit
			$neededMemory = $attachment->width * $attachment->height * ($attachment->fileType == 'image/png' ? 4 : 3) * 2.1;
			if (FileUtil::getMemoryLimit() != -1 && FileUtil::getMemoryLimit() < (memory_get_usage() + $neededMemory)) {
				continue;
			}
			
			$adapter->loadFile($attachment->getLocation());
			$updateData = array();
			// remove / reset old thumbnails
			if ($attachment->tinyThumbnailType) {
				@unlink($attachment->getTinyThumbnailLocation());
				$updateData['tinyThumbnailType'] = '';
				$updateData['tinyThumbnailSize'] = 0;
				$updateData['tinyThumbnailWidth'] = 0;
				$updateData['tinyThumbnailHeight'] = 0;
			}
			if ($attachment->thumbnailType) {
				@unlink($attachment->getThumbnailLocation());
				$updateData['thumbnailType'] = '';
				$updateData['thumbnailSize'] = 0;
				$updateData['thumbnailWidth'] = 0;
				$updateData['thumbnailHeight'] = 0;
			}
			
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
				$thumbnail = $adapter->createThumbnail(ATTACHMENT_THUMBNAIL_WIDTH, ATTACHMENT_THUMBNAIL_HEIGHT, ATTACHMENT_RETAIN_DIMENSIONS);
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
	
	/**
	 * Validates parameters to update the attachments show order.
	 */
	public function validateUpdatePosition() {
		$this->readInteger('objectID', true);
		$this->readString('objectType');
		$this->readString('tmpHash', true);
		
		if (empty($this->parameters['objectID']) && empty($this->parameters['tmpHash'])) {
			throw new UserInputException('objectID');
		}
		
		// validate object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['objectType']);
		if ($objectType === null) {
			throw new UserInputException('objectType');
		}
		
		if (!empty($this->parameters['objectID'])) {
			// check upload permissions
			if (!$objectType->getProcessor()->canUpload($this->parameters['objectID'])) {
				throw new PermissionDeniedException();
			}
		}
		
		if (!isset($this->parameters['attachmentIDs']) || !is_array($this->parameters['attachmentIDs'])) {
			throw new UserInputException('attachmentIDs');
		}
		
		$this->parameters['attachmentIDs'] = ArrayUtil::toIntegerArray($this->parameters['attachmentIDs']);
		
		// check attachment ids
		$attachmentIDs = array();
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("attachmentID IN (?)", array($this->parameters['attachmentIDs']));
		$conditions->add("objectTypeID = ?", array($objectType->objectTypeID));
		
		if (!empty($this->parameters['objectID'])) {
			$conditions->add("objectID = ?", array($this->parameters['objectID']));
		}
		else {
			$conditions->add("tmpHash = ?", array($this->parameters['tmpHash']));
		}
		
		$sql = "SELECT	attachmentID
			FROM	wcf".WCF_N."_attachment
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$attachmentIDs[] = $row['attachmentID'];
		}
		
		foreach ($this->parameters['attachmentIDs'] as $attachmentID) {
			if (!in_array($attachmentID, $attachmentIDs)) {
				throw new UserInputException('attachmentIDs');
			}
		}
	}
	
	/**
	 * Updates the attachments show order.
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	showOrder = ?
			WHERE	attachmentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		$showOrder = 1;
		foreach ($this->parameters['attachmentIDs'] as $attachmentID) {
			$statement->execute(array(
				$showOrder++,
				$attachmentID
			));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Copies attachments from one object id to another.
	 */
	public function copy() {
		$sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['sourceObjectType']);
		$targetObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['targetObjectType']);
		
		$attachmentList = new AttachmentList();
		$attachmentList->getConditionBuilder()->add("attachment.objectTypeID = ?", array($sourceObjectType->objectTypeID));
		$attachmentList->getConditionBuilder()->add("attachment.objectID = ?", array($this->parameters['sourceObjectID']));
		$attachmentList->readObjects();
		
		$newAttachmentIDs = array();
		foreach ($attachmentList as $attachment) {
			$newAttachment = AttachmentEditor::create(array(
				'objectTypeID' => $targetObjectType->objectTypeID,
				'objectID' => $this->parameters['targetObjectID'],
				'userID' => $attachment->userID,
				'filename' => $attachment->filename,
				'filesize' => $attachment->filesize,
				'fileType' => $attachment->fileType,
				'fileHash' => $attachment->fileHash,
				'isImage' => $attachment->isImage,
				'width' => $attachment->width,
				'height' => $attachment->height,
				'tinyThumbnailType' => $attachment->tinyThumbnailType,
				'tinyThumbnailSize' => $attachment->tinyThumbnailSize,
				'tinyThumbnailWidth' => $attachment->tinyThumbnailWidth,
				'tinyThumbnailHeight' => $attachment->tinyThumbnailHeight,
				'thumbnailType' => $attachment->thumbnailType,
				'thumbnailSize' => $attachment->thumbnailSize,
				'thumbnailWidth' => $attachment->thumbnailWidth,
				'thumbnailHeight' => $attachment->thumbnailHeight,
				'downloads' => $attachment->downloads,
				'lastDownloadTime' => $attachment->lastDownloadTime,
				'uploadTime' => $attachment->uploadTime,
				'showOrder' => $attachment->showOrder
			));
			
			// copy attachment
			@copy($attachment->getLocation(), $newAttachment->getLocation());
			
			if ($attachment->tinyThumbnailSize) {
				@copy($attachment->getTinyThumbnailLocation(), $newAttachment->getTinyThumbnailLocation());
			}
			if ($attachment->thumbnailSize) {
				@copy($attachment->getThumbnailLocation(), $newAttachment->getThumbnailLocation());
			}
			
			$newAttachmentIDs[$attachment->attachmentID] = $newAttachment->attachmentID;
		}
		
		return array(
			'attachmentIDs' => $newAttachmentIDs
		);
	}
}
