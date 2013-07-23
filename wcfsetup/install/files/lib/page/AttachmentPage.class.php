<?php
namespace wcf\page;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class AttachmentPage extends AbstractFileDownloadPage {	
	/**
	 * attachment id
	 * @var	integer
	 */
	public $attachmentID = 0;
	
	/**
	 * attachment object
	 * @var	wcf\data\attachment\Attachment
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
	 * list of mime types which belong to files that are displayed inline
	 * @var	array<string>
	 */
	public static $inlineMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'image/x-png', 'application/pdf', 'image/pjpeg');
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->attachmentID = intval($_REQUEST['id']);
		$this->attachment = new Attachment($this->attachmentID);
		if (!$this->attachment->attachmentID) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['tiny']) && $this->attachment->tinyThumbnailType) $this->tiny = intval($_REQUEST['tiny']);
		if (isset($_REQUEST['thumbnail']) && $this->attachment->thumbnailType) $this->thumbnail = intval($_REQUEST['thumbnail']);
	}
	
	/**
	 * @see	wcf\page\IPage::checkPermissions()
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
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get file data
		if ($this->tiny) {
			$this->mimeType = $this->attachment->tinyThumbnailType;
			$this->filesize = $this->attachment->tinyThumbnailSize;
			$this->location = $this->attachment->getTinyThumbnailLocation();
		}
		else if ($this->thumbnail) {
			$this->mimeType = $this->attachment->thumbnailType;
			$this->filesize = $this->attachment->thumbnailSize;
			$this->location = $this->attachment->getThumbnailLocation();
		}
		else {
			$this->mimeType = $this->attachment->fileType;
			$this->filesize = $this->attachment->filesize;
			$this->location = $this->attachment->getLocation();
		}
		
		// set file data
		$this->filename = $this->attachment->filename;
		$this->fileIdentifier = $this->attachment->attachmentID;
		$this->lastModificationTime = $this->attachment->uploadTime;
		
		// check if mime type is inline mime type
		if (in_array($this->mimeType, self::$inlineMimeTypes)) {
			$this->showInline = true;
		}
		
		if (!$this->tiny && !$this->thumbnail) {
			// update download count
			$editor = new AttachmentEditor($this->attachment);
			$editor->update(array(
				'downloads' => $this->attachment->downloads + 1,
				'lastDownloadTime' => TIME_NOW
			));
		}
		else {
			// disable range support for thumbnails
			$this->enableRangeSupport = false;
		}
	}
}
