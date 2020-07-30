<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IUploadAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\upload\DefaultUploadFileSaveStrategy;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;

/**
 * Executes attachment-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Attachment
 * 
 * @method	Attachment		create()
 * @method	AttachmentEditor[]	getObjects()
 * @method	AttachmentEditor	getSingleObject()
 */
class AttachmentAction extends AbstractDatabaseObjectAction implements ISortableAction, IUploadAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['delete', 'updatePosition', 'upload'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = AttachmentEditor::class;
	
	/**
	 * current attachment object, used to communicate with event listeners
	 * @var	Attachment
	 */
	public $eventAttachment = null;
	
	/**
	 * current data, used to communicate with event listeners.
	 * @var	array
	 */
	public $eventData = [];
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $attachment) {
			if ($attachment->tmpHash) {
				if ($attachment->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
			}
			else if (!$attachment->canDelete()) {
				// admin can always delete attachments (unless they are private)
				if (!WCF::getSession()->getPermission('admin.attachment.canManageAttachment') || ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID)->private) {
					throw new PermissionDeniedException();
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpload() {
		// IE<10 fallback
		if (isset($_POST['isFallback'])) {
			$this->parameters['objectType'] = isset($_POST['objectType']) ? $_POST['objectType'] : '';
			$this->parameters['objectID'] = isset($_POST['objectID']) ? $_POST['objectID'] : 0;
			$this->parameters['parentObjectID'] = isset($_POST['parentObjectID']) ? $_POST['parentObjectID'] : 0;
			$this->parameters['tmpHash'] = isset($_POST['tmpHash']) ? $_POST['tmpHash'] : '';
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
		/** @noinspection PhpUndefinedMethodInspection */
		if ($handler->count() + count($this->parameters['__files']->getFiles()) > $processor->getMaxCount()) {
			throw new UserInputException('files', 'exceededQuota', [
				'current' => $handler->count(),
				'quota' => $processor->getMaxCount()
			]);
		}
		
		// check max filesize, allowed file extensions etc.
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->validateFiles(new DefaultUploadFileValidationStrategy($processor->getMaxSize(), $processor->getAllowedExtensions()));
	}
	
	/**
	 * @inheritDoc
	 */
	public function upload() {
		// get object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $this->parameters['objectType']);
		
		// save files
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class, [
			'generateThumbnails' => true,
			'rotateImages' => true
		], [
			'objectID' => intval($this->parameters['objectID']),
			'objectTypeID' => $objectType->objectTypeID,
			'tmpHash' => !$this->parameters['objectID'] ? $this->parameters['tmpHash'] : ''
		]);
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->saveFiles($saveStrategy);
		
		/** @var Attachment[] $attachments */
		$attachments = $saveStrategy->getObjects();
		
		// return result
		$result = ['attachments' => [], 'errors' => []];
		if (!empty($attachments)) {
			// get attachment ids
			$attachmentIDs = $attachmentToFileID = [];
			foreach ($attachments as $internalFileID => $attachment) {
				$attachmentIDs[] = $attachment->attachmentID;
				$attachmentToFileID[$attachment->attachmentID] = $internalFileID;
			}
			
			// get attachments from database (check thumbnail status)
			$attachmentList = new AttachmentList();
			$attachmentList->setObjectIDs($attachmentIDs);
			$attachmentList->readObjects();
			
			foreach ($attachmentList as $attachment) {
				$result['attachments'][$attachmentToFileID[$attachment->attachmentID]] = [
					'filename' => $attachment->filename,
					'filesize' => $attachment->filesize,
					'formattedFilesize' => FileUtil::formatFilesize($attachment->filesize),
					'isImage' => $attachment->isImage,
					'attachmentID' => $attachment->attachmentID,
					'tinyURL' => $attachment->tinyThumbnailType ? $attachment->getThumbnailLink('tiny') : '',
					'thumbnailURL' => $attachment->thumbnailType ? $attachment->getThumbnailLink('thumbnail') : '',
					'url' => $attachment->getLink(),
					'height' => $attachment->height,
					'width' => $attachment->width,
					'iconName' => $attachment->getIconName()
				];
			}
		}
		
		/** @noinspection PhpUndefinedMethodInspection */
		/** @var UploadFile[] $files */
		$files = $this->parameters['__files']->getFiles();
		foreach ($files as $file) {
			if ($file->getValidationErrorType()) {
				$result['errors'][$file->getInternalFileID()] = [
					'filename' => $file->getFilename(),
					'filesize' => $file->getFilesize(),
					'errorType' => $file->getValidationErrorType(),
					'additionalData' => $file->getValidationErrorAdditionalData()
				];
			}
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
		
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class);
		
		foreach ($this->getObjects() as $attachment) {
			if (!$attachment->isImage) {
				// create thumbnails for every file that isn't an image
				$this->eventAttachment = $attachment;
				$this->eventData = [];
				
				EventHandler::getInstance()->fireAction($this, 'generateThumbnail');
				
				if (!empty($this->eventData)) {
					$attachment->update($this->eventData);
				}
				
				continue;
			}
			
			$saveStrategy->generateThumbnails($attachment->getDecoratedObject());
		}
	}
	
	/**
	 * @inheritDoc
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
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("attachmentID IN (?)", [$this->parameters['attachmentIDs']]);
		$conditions->add("objectTypeID = ?", [$objectType->objectTypeID]);
		
		if (!empty($this->parameters['objectID'])) {
			$conditions->add("objectID = ?", [$this->parameters['objectID']]);
		}
		else {
			$conditions->add("tmpHash = ?", [$this->parameters['tmpHash']]);
		}
		
		$sql = "SELECT	attachmentID
			FROM	wcf".WCF_N."_attachment
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$attachmentIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		foreach ($this->parameters['attachmentIDs'] as $attachmentID) {
			if (!in_array($attachmentID, $attachmentIDs)) {
				throw new UserInputException('attachmentIDs');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	showOrder = ?
			WHERE	attachmentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		$showOrder = 1;
		foreach ($this->parameters['attachmentIDs'] as $attachmentID) {
			$statement->execute([
				$showOrder++,
				$attachmentID
			]);
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
		$attachmentList->getConditionBuilder()->add("attachment.objectTypeID = ?", [$sourceObjectType->objectTypeID]);
		$attachmentList->getConditionBuilder()->add("attachment.objectID = ?", [$this->parameters['sourceObjectID']]);
		$attachmentList->readObjects();
		
		$newAttachmentIDs = [];
		foreach ($attachmentList as $attachment) {
			$newAttachment = AttachmentEditor::create([
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
			]);
			
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
		
		return [
			'attachmentIDs' => $newAttachmentIDs
		];
	}
}
