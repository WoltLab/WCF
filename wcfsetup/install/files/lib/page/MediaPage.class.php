<?php
namespace wcf\page;
use wcf\data\article\Article;
use wcf\data\box\Box;
use wcf\data\media\Media;
use wcf\data\IMessage;
use wcf\system\box\BoxHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\FileReader;
use wcf\util\StringUtil;

/**
 * Shows a media file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class MediaPage extends AbstractPage {
	/**
	 * article which uses the media file as the main article image
	 * @var	Article|null
	 */
	public $article;
	
	/**
	 * id of the article which uses the media file as the main article image
	 * @var	integer
	 */
	public $articleID = 0;
	
	/**
	 * box which uses the media file as box image
	 * @var	Box
	 */
	public $box;
	
	/**
	 * id of the box which uses the media file as box image
	 * @var	integer
	 */
	public $boxID = 0;
	
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
	 * message in which the media is embedded
	 * @var	IMessage
	 */
	public $message;
	
	/**
	 * id of the message in which the media is embedded
	 * @var	integer
	 */
	public $messageID = 0;
	
	/**
	 * name of the object type of the message in which the media is embedded
	 * @var	string
	 */
	public $messageObjectType = '';
	
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
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
			if ($this->articleID) {
				$this->article = new Article($this->articleID);
				
				if (!$this->article->articleID || !$this->article->canRead()) {
					throw new PermissionDeniedException();
				}
			}
			else if ($this->boxID) {
				$this->box = BoxHandler::getInstance()->getBox($this->boxID);
				
				if ($this->box === null || !$this->box->isAccessible()) {
					throw new PermissionDeniedException();
				}
			}
			else if ($this->messageID) {
				MessageEmbeddedObjectManager::getInstance()->loadObjects($this->messageObjectType, [$this->messageID]);
				$this->message = MessageEmbeddedObjectManager::getInstance()->getObject($this->messageObjectType, $this->messageID);
				if ($this->message === null || !($this->message instanceof IMessage) || !$this->message->isVisible()) {
					throw new PermissionDeniedException();
				}
			}
			else {
				$parameters = ['canAccess' => false];
				
				EventHandler::getInstance()->fireAction($this, 'checkMediaAccess', $parameters);
				
				if (empty($parameters['canAccess'])) {
					throw new PermissionDeniedException();
				}
			}
		}
	}
	
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
	 * @inheritDoc
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
		
		if ($this->thumbnail && !$this->media->{$this->thumbnail.'ThumbnailType'}) {
			$this->thumbnail = '';
		}
		
		// read context parameters
		if (isset($_REQUEST['articleID'])) {
			$this->articleID = intval($_REQUEST['articleID']);
		}
		else if (isset($_REQUEST['boxID'])) {
			$this->boxID = intval($_REQUEST['boxID']);
		}
		else if (isset($_REQUEST['messageObjectType']) && isset($_REQUEST['messageID'])) {
			$this->messageObjectType = StringUtil::trim($_REQUEST['messageObjectType']);
			$this->messageID = intval($_REQUEST['messageID']);
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
		
		// send file to client
		$this->fileReader->send();
		exit;
	}
}
