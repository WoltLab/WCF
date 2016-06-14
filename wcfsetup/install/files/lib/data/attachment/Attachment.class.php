<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\IThumbnailFile;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Attachment
 *
 * @property-read	integer		$attachmentID
 * @property-read	integer		$objectTypeID
 * @property-read	integer|null	$objectID
 * @property-read	integer|null	$userID
 * @property-read	string		$tmpHash
 * @property-read	string		$filename
 * @property-read	integer		$filesize
 * @property-read	string		$fileType
 * @property-read	string		$fileHash
 * @property-read	integer		$isImage
 * @property-read	integer		$width
 * @property-read	integer		$height
 * @property-read	string		$tinyThumbnailType
 * @property-read	integer		$tinyThumbnailSize
 * @property-read	integer		$tinyThumbnailWidth
 * @property-read	integer		$tinyThumbnailHeight
 * @property-read	string		$thumbnailType
 * @property-read	integer		$thumbnailSize
 * @property-read	integer		$thumbnailWidth
 * @property-read	integer		$thumbnailHeight
 * @property-read	integer		$downloads
 * @property-read	integer		$lastDownloadTime
 * @property-read	integer		$uploadTime
 * @property-read	integer		$showOrder
 */
class Attachment extends DatabaseObject implements IRouteController, IThumbnailFile {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'attachment';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'attachmentID';
	
	/**
	 * indicates if the attachment is embedded
	 * @var	boolean
	 */
	protected $embedded = false;
	
	/**
	 * user permissions for attachment access
	 * @var	boolean[]
	 */
	protected $permissions = [];
	
	/**
	 * Returns true if a user has the permission to download this attachment.
	 * 
	 * @return	boolean
	 */
	public function canDownload() {
		return $this->getPermission('canDownload');
	}
	
	/**
	 * Returns true if a user has the permission to view the preview of this
	 * attachment.
	 * 
	 * @return	boolean
	 */
	public function canViewPreview() {
		return $this->getPermission('canViewPreview');
	}
	
	/**
	 * Returns true if a user has the permission to delete the preview of this
	 * attachment.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		return $this->getPermission('canDelete');
	}
	
	/**
	 * Checks permissions.
	 * 
	 * @param	string		$permission
	 * @return	boolean
	 */
	protected function getPermission($permission) {
		if (!isset($this->permissions[$permission])) {
			$this->permissions[$permission] = true;
			
			if ($this->tmpHash) {
				if ($this->userID && $this->userID != WCF::getUser()->userID) {
					$this->permissions[$permission] = false;
				}
			}
			else {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
				$processor = $objectType->getProcessor();
				if ($processor !== null) {
					$this->permissions[$permission] = call_user_func([$processor, $permission], $this->objectID);
				}
			}
		}
		
		return $this->permissions[$permission];
	}
	
	/**
	 * Sets the permissions for attachment access.
	 * 
	 * @param	boolean[]		$permissions
	 */
	public function setPermissions(array $permissions) {
		$this->permissions = $permissions;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLocation() {
		return self::getStorage() . substr($this->fileHash, 0, 2) . '/' . ($this->attachmentID) . '-' . $this->fileHash;
	}
	
	/**
	 * Returns the physical location of the tiny thumbnail.
	 * 
	 * @return	string
	 */
	public function getTinyThumbnailLocation() {
		return $this->getThumbnailLocation('tiny');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getThumbnailLocation($size = '') {
		if ($size == 'tiny') {
			return self::getStorage() . substr($this->fileHash, 0, 2) . '/' . ($this->attachmentID) . '-tiny-' . $this->fileHash;
		}
		
		return self::getStorage() . substr($this->fileHash, 0, 2) . '/' . ($this->attachmentID) . '-thumbnail-' . $this->fileHash;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getThumbnailLink($size = '') {
		$parameters = [
			'id' => $this->attachmentID
		];
		
		if ($size == 'tiny') {
			$parameters['tiny'] = 1;
		}
		
		return LinkHandler::getInstance()->getLink('Attachment', $parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->filename;
	}
	
	/**
	 * Marks this attachment as embedded.
	 * 
	 * @return	boolean
	 */
	public function markAsEmbedded() {
		$this->embedded = true;
	}
	
	/**
	 * Returns true if this attachment is embedded.
	 * 
	 * @return	boolean
	 */
	public function isEmbedded() {
		return $this->embedded;
	}
	
	/**
	 * Returns true if this attachment should be shown as an image.
	 * 
	 * @return	boolean
	 */
	public function showAsImage() {
		if ($this->isImage) {
			if (!$this->hasThumbnail() && ($this->width > ATTACHMENT_THUMBNAIL_WIDTH || $this->height > ATTACHMENT_THUMBNAIL_HEIGHT)) return false;
			
			if ($this->canDownload()) return true;
			
			if ($this->canViewPreview() && $this->hasThumbnail()) return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if this attachment has a thumbnail.
	 * 
	 * @return	boolean
	 */
	public function hasThumbnail() {
		return ($this->thumbnailType ? true : false);
	}
	
	/**
	 * Returns true if this attachment should be shown as a file.
	 * 
	 * @return	boolean
	 */
	public function showAsFile() {
		return !$this->showAsImage();
	}
	
	/**
	 * Returns the storage path.
	 * 
	 * @return	string
	 */
	public static function getStorage() {
		if (ATTACHMENT_STORAGE) {
			return FileUtil::addTrailingSlash(ATTACHMENT_STORAGE);
		}
		
		return WCF_DIR . 'attachments/';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getThumbnailSizes() {
		return [
			'tiny' => [
				'height' => 144,
				'retainDimensions' => false,
				'width' => 144
			],
			// standard thumbnail size
			'' => [
				'height' => ATTACHMENT_THUMBNAIL_HEIGHT,
				'retainDimensions' => ATTACHMENT_RETAIN_DIMENSIONS,
				'width' => ATTACHMENT_THUMBNAIL_WIDTH
			]
		];
	}
}
