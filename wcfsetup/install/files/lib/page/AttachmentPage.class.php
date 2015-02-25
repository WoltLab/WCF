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
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class AttachmentPage extends AbstractPage {
	/**
	 * @see	\wcf\page\IPage::$useTemplate
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
	 * @var	array<string>
	 */
	public static $inlineMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'image/x-png', 'application/pdf', 'image/pjpeg');
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->attachmentID = intval($_REQUEST['id']);
		$this->attachment = new Attachment($this->attachmentID);
		if (!$this->attachment->attachmentID) {
			throw new IllegalLinkException();
		}
		
		$parameters = array('object' => $this->attachment);
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
	 * @see	\wcf\page\IPage::checkPermissions()
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
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get file data
		if ($this->tiny) {
			$mimeType = $this->attachment->tinyThumbnailType;
			$filesize = $this->attachment->tinyThumbnailSize;
			$location = $this->attachment->getTinyThumbnailLocation();
		}
		else if ($this->thumbnail) {
			$mimeType = $this->attachment->thumbnailType;
			$filesize = $this->attachment->thumbnailSize;
			$location = $this->attachment->getThumbnailLocation();
		}
		else {
			$mimeType = $this->attachment->fileType;
			$filesize = $this->attachment->filesize;
			$location = $this->attachment->getLocation();
		}
		
		// init file reader
		$this->fileReader = new FileReader($location, array(
			'filename' => $this->attachment->filename,
			'mimeType' => $mimeType,
			'filesize' => $filesize,
			'showInline' => (in_array($mimeType, self::$inlineMimeTypes)),
			'enableRangeSupport' => (!$this->tiny && !$this->thumbnail),
			'lastModificationTime' => $this->attachment->uploadTime,
			'expirationDate' => TIME_NOW + 31536000,
			'maxAge' => 31536000
		));
		
		// add etag for non-thumbnail
		if (!$this->thumbnail && !$this->tiny) {
			$this->fileReader->addHeader('ETag', '"'.$this->attachmentID.'"');
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		if (!$this->tiny && !$this->thumbnail) {
			// update download count
			$editor = new AttachmentEditor($this->attachment);
			$editor->update(array(
				'downloads' => $this->attachment->downloads + 1,
				'lastDownloadTime' => TIME_NOW
			));
		}
		
		// send file to client
		$this->fileReader->send();
		exit;
	}
}
