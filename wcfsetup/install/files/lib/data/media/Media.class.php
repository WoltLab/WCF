<?php
namespace wcf\data\media;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\IThumbnailFile;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a media file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 * 
 * @property-read	integer		$mediaID
 * @property-read	string		$filename
 * @property-read	integer		$filesize
 * @property-read	string		$fileType
 * @property-read	string		$fileHash
 * @property-read	integer		$uploadTime
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	integer|null	$languageID
 * @property-read	integer		$isMultilingual
 * @property-read	integer		$isImage
 * @property-read	integer		$width
 * @property-read	integer		$height
 * @property-read	string		$tinyThumbnailType
 * @property-read	integer		$tinyThumbnailSize
 * @property-read	integer		$tinyThumbnailWidth
 * @property-read	integer		$tinyThumbnailHeight
 * @property-read	string		$smallThumbnailType
 * @property-read	integer		$smallThumbnailSize
 * @property-read	integer		$smallThumbnailWidth
 * @property-read	integer		$smallThumbnailHeight
 * @property-read	string		$mediumThumbnailType
 * @property-read	integer		$mediumThumbnailSize
 * @property-read	integer		$mediumThumbnailWidth
 * @property-read	integer		$mediumThumbnailHeight
 * @property-read	string		$largeThumbnailType
 * @property-read	integer		$largeThumbnailSize
 * @property-read	integer		$largeThumbnailWidth
 * @property-read	integer		$largeThumbnailHeight
 */
class Media extends DatabaseObject implements ILinkableObject, IRouteController, IThumbnailFile {
	/**
	 * i18n media data grouped by language id for all language
	 * @var	string[][]
	 */
	protected $i18nData;
	
	/**
	 * parameters used to build the link to the media file
	 * @var	array
	 */
	protected $linkParameters = [];
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'media';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'mediaID';

	/**
	 * data of the different thumbnail sizes
	 * @var	array
	 */
	protected static $thumbnailSizes = [
		'tiny' => [
			'height' => 144,
			'retainDimensions' => false,
			'width' => 144
		],
		'small' => [
			'height' => MEDIA_SMALL_THUMBNAIL_HEIGHT,
			'retainDimensions' => MEDIA_SMALL_THUMBNAIL_RETAIN_DIMENSIONS,
			'width' => MEDIA_SMALL_THUMBNAIL_WIDTH
		],
		'medium' => [
			'height' => MEDIA_MEDIUM_THUMBNAIL_HEIGHT,
			'retainDimensions' => MEDIA_MEDIUM_THUMBNAIL_RETAIN_DIMENSIONS,
			'width' => MEDIA_MEDIUM_THUMBNAIL_WIDTH
		],
		'large' => [
			'height' => MEDIA_LARGE_THUMBNAIL_HEIGHT,
			'retainDimensions' => MEDIA_LARGE_THUMBNAIL_RETAIN_DIMENSIONS,
			'width' => MEDIA_LARGE_THUMBNAIL_WIDTH
		]
	];
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Media', array_merge($this->linkParameters, [
			'object' => $this
		]));
	}
	
	/**
	 * Sets additional parameters used to build the link to the media file.
	 * 
	 * @param	array		$parameters
	 */
	public function setLinkParameters(array $parameters) {
		$this->linkParameters = $parameters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLocation() {
		return self::getStorage().substr($this->fileHash, 0, 2).'/'.$this->mediaID.'-'.$this->fileHash;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getThumbnailLink($size) {
		if (!isset(self::$thumbnailSizes[$size])) {
			throw new \InvalidArgumentException("Unknown thumbnail size '".$size."'");
		}
		
		if (!$this->{$size.'ThumbnailType'}) {
			return $this->getLink();
		}
		
		return LinkHandler::getInstance()->getLink('Media', array_merge($this->linkParameters, [
			'object' => $this,
			'thumbnail' => $size
		]));
	}
	
	/**
	 * Returns the width of the thumbnail file with the given size.
	 *
	 * @param	string		$size
	 * @return	integer
	 * @throws	\InvalidArgumentException
	 */
	public function getThumbnailWidth($size) {
		if (!isset(self::$thumbnailSizes[$size])) {
			throw new \InvalidArgumentException("Unknown thumbnail size '".$size."'");
		}
		
		if ($this->{$size.'ThumbnailType'}) {
			return $this->{$size.'ThumbnailWidth'};
		}
		
		return $this->width;
	}
	
	/**
	 * Returns the height of the thumbnail file with the given size.
	 *
	 * @param	string		$size
	 * @return	integer
	 * @throws	\InvalidArgumentException
	 */
	public function getThumbnailHeight($size) {
		if (!isset(self::$thumbnailSizes[$size])) {
			throw new \InvalidArgumentException("Unknown thumbnail size '".$size."'");
		}
		
		if ($this->{$size.'ThumbnailType'}) {
			return $this->{$size.'ThumbnailHeight'};
		}
		
		return $this->height;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getThumbnailLocation($size) {
		if (!isset(self::$thumbnailSizes[$size])) {
			throw new \InvalidArgumentException("Unknown thumbnail size '".$size."'");
		}
		
		return self::getStorage().substr($this->fileHash, 0, 2).'/'.$this->mediaID.'-'.$size.'-'.$this->fileHash;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->filename;
	}
	
	/**
	 * Returns the i18n media data grouped by language id for all language.
	 * 
	 * @return	string[][]
	 */
	public function getI18nData() {
		if ($this->i18nData === null) {
			$this->i18nData = [
				'altText' => [],
				'caption' => [],
				'title' => []
			];
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_media_content
				WHERE	mediaID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->mediaID]);
			
			while ($row = $statement->fetchArray()) {
				$this->i18nData['altText'][$row['languageID']] = $row['altText'];
				$this->i18nData['caption'][$row['languageID']] = $row['caption'];
				$this->i18nData['title'][$row['languageID']] = $row['title'];
			}
		}
		
		return $this->i18nData;
	}
	
	/**
	 * Returns the storage path of the media files.
	 * 
	 * @return	string
	 */
	public static function getStorage() {
		return WCF_DIR.'media/';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getThumbnailSizes() {
		return static::$thumbnailSizes;
	}
}
