<?php
namespace wcf\page;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\io\File;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Shows an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.attachment
 * @subpackage	page
 * @category	Community Framework
 */
class AttachmentPage extends AbstractPage {
	/**
	 * @see	wcf\page\IPage::$useTemplate
	 */
	public $useTemplate = false;
	
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
	public static $inlineMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg');
	
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
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		// update download count
		if (!$this->tiny && !$this->thumbnail) {
			$editor = new AttachmentEditor($this->attachment);
			$editor->update(array(
				'downloads' => $this->attachment->downloads + 1,
				'lastDownloadTime' => TIME_NOW
			));
		}
		
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
		
		// range support
		$startByte = 0;
		$endByte = $filesize - 1;
		if (!$this->tiny && !$this->thumbnail) {
			if (!empty($_SERVER['HTTP_RANGE'])) {
				$regex = new Regex('^bytes=(-?\d+)(?:-(\d+))?$');
				if ($regex->match($_SERVER['HTTP_RANGE'])) {
					$matches = $regex->getMatches();
					$first = intval($matches[1]);
					$last = (isset($matches[2]) ? intval($matches[2]) : 0);
					
					if ($first < 0) {
						// negative value; subtract from filesize
						$startByte = $filesize + $first;
					}
					else {
						$startByte = $first;
						if ($last > 0) {
							$endByte = $last;
						}
					}
					
					// validate given range
					if ($startByte < 0 || $startByte >= $filesize || $endByte >= $filesize) {
						// invalid range given
						@header('HTTP/1.1 416 Requested Range Not Satisfiable');
						@header('Accept-Ranges: bytes');
						@header('Content-Range: bytes */'.$filesize);
						exit;
					}
				}
			}
		}
		
		// send headers
		// file type
		if ($mimeType == 'image/x-png') $mimeType = 'image/png';
		@header('Content-Type: '.$mimeType);
		
		// file name
		@header('Content-disposition: '.(!in_array($mimeType, self::$inlineMimeTypes) ? 'attachment; ' : 'inline; ').'filename="'.$this->attachment->filename.'"');
		
		// range
		if ($startByte > 0 || $endByte < $filesize - 1) {
			@header('HTTP/1.1 206 Partial Content');
			@header('Content-Range: bytes '.$startByte.'-'.$endByte.'/'.$filesize);
		}
		if (!$this->tiny && !$this->thumbnail) {
			@header('ETag: "'.$this->attachmentID.'"');
			@header('Accept-Ranges: bytes');
		}
		
		// send file size
		@header('Content-Length: '.($endByte + 1 - $startByte));
		
		// cache headers
		@header('Cache-control: max-age=31536000, private');
		@header('Expires: '.gmdate('D, d M Y H:i:s', TIME_NOW + 31536000).' GMT');
		@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->attachment->uploadTime).' GMT');
		
		// show attachment
		if ($startByte > 0 || $endByte < $filesize - 1) {
			$file = new File($location, 'rb');
			if ($startByte > 0) $file->seek($startByte);
			while ($startByte <= $endByte) {
				$remainingBytes = $endByte - $startByte;
				$readBytes = ($remainingBytes > 1048576) ? 1048576 : $remainingBytes + 1;
				echo $file->read($readBytes);
				$startByte += $readBytes;
			}
			$file->close();
		}
		else {
			readfile($location);
		}
		exit;
	}
}
