<?php
namespace wcf\page;
use wcf\data\media\Media;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\util\FileReader;
use wcf\util\StringUtil;

/**
 * Shows a media file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 * @since	2.2
 */
class MediaPage extends AbstractPage {
	/**
	 * etag for the media file
	 * @var	string
	 */
	public $eTag = null;
	
	/**
	 * file reader object
	 * @var	FileReader
	 */
	public $fileReader = null;
	
	/**
	 * requested media file
	 * @var	Media
	 */
	public $media = null;
	
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
	 * @inheritdoc
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
	
	// TODO: remove the following line once method is implemented
	// @codingStandardsIgnoreStart
	/**
	 * @inheritdoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		// TODO
	}
	// TODO: remove the following line once method is implemented
	// @codingStandardsIgnoreEnd
	
	/**
	 * @inheritdoc
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
		
		// init file reader
		$this->fileReader = new FileReader($location, [
			'filename' => $this->media->filename,
			'mimeType' => $mimeType,
			'filesize' => $filesize,
			'showInline' => (in_array($mimeType, self::$inlineMimeTypes)),
			'enableRangeSupport' => ($this->thumbnail ? true : false),
			'lastModificationTime' => $this->media->uploadTime,
			'expirationDate' => TIME_NOW + 31536000,
			'maxAge' => 31536000
		]);
		
		if ($this->eTag !== null) {
			$this->fileReader->addHeader('ETag', '"'.$this->eTag.'"');
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->mediaID = intval($_REQUEST['id']);
		$this->media = new Media($this->mediaID);
		if (!$this->media->mediaID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['thumbnail'])) $this->thumbnail = StringUtil::trim($_REQUEST['thumbnail']);
		if ($this->thumbnail && !isset(Media::getThumbnailSizes()[$this->thumbnail])) {
			throw new IllegalLinkException();
		}
		
		$parameters = [
			'object' => $this->media
		];
		if ($this->thumbnail && $this->media->{$this->thumbnail.'ThumbnailType'}) {
			$parameters['thumbnail'] = $this->thumbnail;
		}
		else {
			$this->thumbnail = '';
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('Media', $parameters);
	}
	
	/**
	 * @inheritdoc
	 */
	public function show() {
		parent::show();
		
		// etag caching
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == '"'.$this->eTag.'"') {
			@header('HTTP/1.1 304 Not Modified');
			exit;
		}
		
		// send file to client
		$this->fileReader->send();
		exit;
	}
}
