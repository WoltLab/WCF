<?php
namespace wcf\data\attachment;
use wcf\data\ILinkableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\IThumbnailFile;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Attachment
 *
 * @property-read	integer		$attachmentID		unique id of the attachment
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.attachment.objectType` object type
 * @property-read	integer|null	$objectID		id of the attachment container object the attachment belongs to
 * @property-read	integer|null	$userID			id of the user who uploaded the attachment or `null` if the user does not exist anymore or if the attachment has been uploaded by a guest
 * @property-read	string		$tmpHash		temporary hash used to identify uploaded attachments but not associated with an object yet or empty if the attachment has been associated with an object
 * @property-read	string		$filename		name of the physical attachment file
 * @property-read	integer		$filesize		size of the physical attachment file
 * @property-read	string		$fileType		type of the physical attachment file
 * @property-read	string		$fileHash		hash of the physical attachment file
 * @property-read	integer		$isImage		is `1` if the attachment is an image, otherwise `0`
 * @property-read	integer		$width			width of the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$height			height of the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	string		$tinyThumbnailType	type of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise empty
 * @property-read	integer		$tinyThumbnailSize	size of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$tinyThumbnailWidth	width of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$tinyThumbnailHeight	height of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	string		$thumbnailType	type of the thumbnail file for the attachment if `$isImage` is `1`, otherwise empty
 * @property-read	integer		$thumbnailSize	size of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$thumbnailWidth	width of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$thumbnailHeight	height of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$downloads		number of times the attachment has been downloaded
 * @property-read	integer		$lastDownloadTime	timestamp at which the attachment has been downloaded the last time
 * @property-read	integer		$uploadTime		timestamp at which the attachment has been uploaded
 * @property-read	integer		$showOrder		position of the attachment in relation to the other attachment to the same message
 */
class Attachment extends DatabaseObject implements ILinkableObject, IRouteController, IThumbnailFile {
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
	 * @inheritDoc
	 */
	public function getLink() {
		// Do not use `LinkHandler::getControllerLink()` or `forceFrontend` as attachment
		// links can be opened in the frontend and in the ACP.
		return LinkHandler::getInstance()->getLink('Attachment', [
			'object' => $this,
		]);
	}
	
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
		return $this->getLocationHelper(self::getStorage() . substr($this->fileHash, 0, 2) . '/' . $this->attachmentID . '-' . $this->fileHash);
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
			$location = self::getStorage() . substr($this->fileHash, 0, 2) . '/' . $this->attachmentID . '-tiny-' . $this->fileHash;
		}
		else {
			$location = self::getStorage() . substr($this->fileHash, 0, 2) . '/' . $this->attachmentID . '-thumbnail-' . $this->fileHash;
		}
		
		return $this->getLocationHelper($location);
	}
	
	/**
	 * Migrates the storage location of this attachment to
	 * include the `.bin` suffix.
	 * 
	 * @since	5.2
	 */
	public function migrateStorage() {
		foreach ([$this->getLocation(), $this->getThumbnailLocation(), $this->getThumbnailLocation('tiny')] as $location) {
			if (!StringUtil::endsWith($location, '.bin')) {
				rename($location, $location.'.bin');
			}
		}
	}
	
	/**
	 * Returns the appropriate location with or without extension.
	 * 
	 * Files are suffixed with `.bin` since 5.2, but they are recognized
	 * without the extension for backward compatibility.
	 * 
	 * @param	string $location
	 * @return	string
	 * @since	5.2
	 */
	protected final function getLocationHelper($location) {
		if (is_readable($location.'.bin')) {
			return $location.'.bin';
		}
		else if (is_readable($location)) {
			return $location;
		}
		
		// Assume that the attachment has not been uploaded yet.
		return $location.'.bin';
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
		else if ($size == 'thumbnail') {
			$parameters['thumbnail'] = 1;
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
	 * Returns icon name for this attachment.
	 * 
	 * @return      string
	 */
	public function getIconName() {
		if ($iconName = FileUtil::getIconNameByFilename($this->filename)) {
			return 'file-' . $iconName . '-o';
		}
		
		return 'paperclip';
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
