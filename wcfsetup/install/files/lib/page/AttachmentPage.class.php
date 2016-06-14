<?php
namespace wcf\page;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileReader;

/**
 * Shows an attachment.
 * 
 * @author	Joshua Ruesweg, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class AttachmentPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $useTemplate = false;
	
	/**
	 * attachment id
	 * @var	integer
	 */
	public $attachmentID = 0;
	
	/**
	 * attachment object
	 * @var	\wcf\data\attachment\Attachment
	 */
	public $attachment = null;
	
	/**
	 * shows the tiny thumbnail
	 * @var	boolean
	 */
	public $tiny = 0;
	
	/**
	 * shows the standard thumbnail
	 * @var	boolean
	 */
	public $thumbnail = 0;
	
	/**
	 * file reader object
	 * @var	\wcf\util\FileReader
	 */
	public $fileReader = null;
	
	/**
	 * list of mime types which belong to files that are displayed inline
	 * @var	string[]
	 */
	public static $inlineMimeTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/x-png', 'application/pdf', 'image/pjpeg'];
	
	/**
	 * etag for this attachment
	 * @var	string
	 */ 
	public $eTag = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->attachmentID = intval($_REQUEST['id']);
		$this->attachment = new Attachment($this->attachmentID);
		if (!$this->attachment->attachmentID) {
			throw new IllegalLinkException();
		}
		
		$parameters = ['object' => $this->attachment];
		if (isset($_REQUEST['tiny']) && $this->attachment->tinyThumbnailType) {
			$this->tiny = intval($_REQUEST['tiny']);
			$parameters['tiny'] = $this->tiny;
		}
		if (isset($_REQUEST['thumbnail']) && $this->attachment->thumbnailType) {
			$this->thumbnail = intval($_REQUEST['thumbnail']);
			$parameters['thumbnail'] = $this->thumbnail;
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('Attachment', $parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if ($this->attachment->tmpHash) {
			if ($this->attachment->userID && $this->attachment->userID != WCF::getUser()->userID) {
				throw new IllegalLinkException();
			}
		}
		else {
			// check permissions
			if ($this->tiny || $this->thumbnail) {
				if (!$this->attachment->canViewPreview()) {
					throw new PermissionDeniedException();
				}
			}
			else if (!$this->attachment->canDownload()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get file data
		if ($this->tiny) {
			$mimeType = $this->attachment->tinyThumbnailType;
			$filesize = $this->attachment->tinyThumbnailSize;
			$location = $this->attachment->getTinyThumbnailLocation();
			$this->eTag = 'TINY_'.$this->attachmentID;
		}
		else if ($this->thumbnail) {
			$mimeType = $this->attachment->thumbnailType;
			$filesize = $this->attachment->thumbnailSize;
			$location = $this->attachment->getThumbnailLocation();
			$this->eTag = 'THUMB_'.$this->attachmentID;
		}
		else {
			$mimeType = $this->attachment->fileType;
			$filesize = $this->attachment->filesize;
			$location = $this->attachment->getLocation();
			$this->eTag = $this->attachmentID;
		}
		
		// init file reader
		$this->fileReader = new FileReader($location, [
			'filename' => $this->attachment->filename,
			'mimeType' => $mimeType,
			'filesize' => $filesize,
			'showInline' => (in_array($mimeType, self::$inlineMimeTypes)),
			'enableRangeSupport' => (!$this->tiny && !$this->thumbnail),
			'lastModificationTime' => $this->attachment->uploadTime,
			'expirationDate' => TIME_NOW + 31536000,
			'maxAge' => 31536000
		]);
		
		if ($this->eTag !== null) {
			$this->fileReader->addHeader('ETag', '"'.$this->eTag.'"');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		parent::show();
		
		// etag caching
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == '"'.$this->eTag.'"') {
			@header('HTTP/1.1 304 Not Modified');
			exit;
		}
		
		if (!$this->tiny && !$this->thumbnail) {
			// update download count
			$editor = new AttachmentEditor($this->attachment);
			$editor->update([
				'downloads' => $this->attachment->downloads + 1,
				'lastDownloadTime' => TIME_NOW
			]);
		}
		
		// send file to client
		$this->fileReader->send();
		exit;
	}
}
