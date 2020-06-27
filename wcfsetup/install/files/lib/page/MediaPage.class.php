<?php
namespace wcf\page;
use wcf\data\media\Media;
use wcf\data\media\MediaEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\util\FileReader;
use wcf\util\StringUtil;

/**
 * Shows a media file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class MediaPage extends AbstractPage {
	/**
	 * etag for the media file
	 * @var	string
	 */
	public $eTag;
	
	/**
	 * file reader object
	 * @var	FileReader
	 */
	public $fileReader;
	
	/**
	 * requested media file
	 * @var	Media
	 */
	public $media;
	
	/**
	 * id of the requested media file
	 * @var	integer
	 */
	public $mediaID = 0;
	
	/**
	 * size of the requested thumbnail
	 * @var	string
	 */
	public $thumbnail = '';
	
	/**
	 * @inheritDoc
	 */
	public $useTemplate = false;
	
	/**
	 * list of mime types which belong to files that are displayed inline
	 * @var	string[]
	 */
	public static $inlineMimeTypes = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/x-png',
		'application/pdf',
		'image/pjpeg'
	];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get file data
		if ($this->thumbnail) {
			$mimeType = $this->media->{$this->thumbnail.'ThumbnailType'};
			$filesize = $this->media->{$this->thumbnail.'ThumbnailSize'};
			$location = $this->media->getThumbnailLocation($this->thumbnail);
			$this->eTag = strtoupper($this->thumbnail).'_'.$this->mediaID;
		}
		else {
			$mimeType = $this->media->fileType;
			$filesize = $this->media->filesize;
			$location = $this->media->getLocation();
			$this->eTag = $this->mediaID;
		}
		
		$this->eTag .= '_' . $this->media->fileHash;
		
		// init file reader
		$maxAge = 3600;
		$this->fileReader = new FileReader($location, [
			'filename' => $this->media->filename,
			'mimeType' => $mimeType,
			'filesize' => $filesize,
			'showInline' => in_array($mimeType, self::$inlineMimeTypes),
			'enableRangeSupport' => $this->thumbnail ? true : false,
			'lastModificationTime' => $this->media->fileUpdateTime ?? $this->media->uploadTime,
			'expirationDate' => TIME_NOW + $maxAge,
			'maxAge' => $maxAge,
		]);
		
		if ($this->eTag !== null) {
			$this->fileReader->addHeader('ETag', '"'.$this->eTag.'"');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->mediaID = intval($_REQUEST['id']);
		$this->media = new Media($this->mediaID);
		if (!$this->media->mediaID) {
			throw new IllegalLinkException();
		}
		if (!$this->media->isAccessible()) {
			throw new PermissionDeniedException();
		}
		
		if (isset($_REQUEST['thumbnail'])) $this->thumbnail = StringUtil::trim($_REQUEST['thumbnail']);
		if ($this->thumbnail === 'original') {
			// The 'original' size is required by the editor, but is not a valid thumbnail size.  
			$this->thumbnail = '';
		}
		if ($this->thumbnail && !isset(Media::getThumbnailSizes()[$this->thumbnail])) {
			throw new IllegalLinkException();
		}
		
		if ($this->thumbnail && !$this->media->{$this->thumbnail.'ThumbnailType'}) {
			$this->thumbnail = '';
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
		
		if (!$this->thumbnail) {
			// update download count
			(new MediaEditor($this->media))->update([
				'downloads' => $this->media->downloads + 1,
				'lastDownloadTime' => TIME_NOW
			]);
		}
		
		// send file to client
		$this->fileReader->send();
		exit;
	}
}
