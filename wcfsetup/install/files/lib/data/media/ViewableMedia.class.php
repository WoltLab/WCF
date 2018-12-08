<?php
namespace wcf\data\media;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a viewable media file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 * 
 * @method	Media	getDecoratedObject()
 * @mixin	Media
 * @property-read	string|null	$title		title of the media file in the active user's language or `null` if object has not been fetched via `ViewableMediaList`
 * @property-read	string|null	$caption	caption of the media file in the active user's language or `null` if object has not been fetched via `ViewableMediaList`
 * @property-read	string|null	$altText	alternative text of the media file in the active user's language or `null` if object has not been fetched via `ViewableMediaList`
 */
class ViewableMedia extends DatabaseObjectDecorator {
	/**
	 * force localized content by language id
	 * @var integer
	 */
	protected $forceLanguageID;
	
	/**
	 * localized content per language id
	 * @var string[][]
	 */
	protected $localizedContent = [];
	
	/**
	 * user profile of the user who uploaded the media file
	 * @var	UserProfile
	 */
	protected $userProfile;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Media::class;
	
	/**
	 * Registers localized content by language id.
	 * 
	 * @param       integer         $languageID
	 * @param       string[]        $content
	 */
	public function setLocalizedContent($languageID, array $content) {
		$this->localizedContent[$languageID] = $content;
	}
	
	/**
	 * Returns an instance of this class with localized versions.
	 * 
	 * @param       integer         $languageID
	 * @return      ViewableMedia
	 */
	public function getLocalizedVersion($languageID) {
		if (isset($this->localizedContent[$languageID])) {
			$localized = clone $this;
			$localized->forceLanguageID($languageID);
			
			return $localized;
		}
		
		return $this;
	}
	
	/**
	 * Forces the localized values by language id.
	 * 
	 * @param       integer         $languageID
	 */
	protected function forceLanguageID($languageID) {
		$this->forceLanguageID = $languageID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		if ($this->forceLanguageID !== null && isset($this->localizedContent[$this->forceLanguageID][$name])) {
			return $this->localizedContent[$this->forceLanguageID][$name];
		}
		
		return $this->object->__get($name);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		if ($this->title) {
			return $this->title;
		}
		
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * Returns a textual representation of the media file to be used in templates.
	 * 
	 * @return	string
	 */
	public function __toString() {
		if ($this->isImage) {
			return '<img src="'.StringUtil::encodeHTML($this->getLink()).'" alt="'.StringUtil::encodeHTML($this->altText).'" '.($this->title ? 'title="'.StringUtil::encodeHTML($this->title).'" ' : '').'/>';
		}
		
		return '<a href="'.StringUtil::encodeHTML($this->getLink()).'">'.StringUtil::encodeHTML($this->getTitle()).'</a>';
	}
	
	/**
	 * Returns a tag to display the media element.
	 * 
	 * @param	string		$size
	 * @return	string
	 */
	public function getElementTag($size) {
		if ($this->isImage && $this->tinyThumbnailType) {
			$tinyThumbnail = Media::getThumbnailSizes()['tiny'];
			if ($size <= $tinyThumbnail['width'] && $size <= $tinyThumbnail['height']) {
				return '<img src="' . StringUtil::encodeHTML($this->getThumbnailLink('tiny')) . '" alt="' . StringUtil::encodeHTML($this->altText) . '" '.($this->title ? 'title="'.StringUtil::encodeHTML($this->title).'" ' : '').'style="width: ' . $size . 'px; height: ' . $size . 'px;">';
			}
		}
		
		$icon = FileUtil::getIconNameByFilename($this->filename);
		return '<span class="icon icon' . $size . ' fa-file' . ($icon ? '-' . $icon : '') . '-o"></span>';
	}
	
	/**
	 * Returns a tag to display a certain thumbnail.
	 * 
	 * @param	string		$size		thumbnail size
	 * @return	string
	 * @throws	\InvalidArgumentException
	 */
	public function getThumbnailTag($size = 'tiny') {
		if (!isset(Media::getThumbnailSizes()[$size])) {
			throw new \InvalidArgumentException("Unknown thumbnail size '".$size."'");
		}
		
		return '<img src="'.StringUtil::encodeHTML($this->getThumbnailLink($size)).'" alt="'.StringUtil::encodeHTML($this->altText).'" '.($this->title ? 'title="'.StringUtil::encodeHTML($this->title).'" ' : '').'style="width: ' . $this->getThumbnailWidth($size) . 'px; height: ' . $this->getThumbnailHeight($size) . 'px;">';
	}
	
	/**
	 * Returns the user profile of the user who uploaded the media file.
	 * 
	 * @return	UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			if ($this->userID) {
				$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
			}
			else {
				$this->userProfile = UserProfile::getGuestUserProfile($this->username);
			}
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns the viewable media file with the given id.
	 * 
	 * @param	integer		$mediaID
	 * @return	ViewableMedia|null
	 */
	public static function getMedia($mediaID) {
		$mediaList = new ViewableMediaList();
		$mediaList->setObjectIDs([$mediaID]);
		$mediaList->readObjects();
		
		return $mediaList->search($mediaID);
	}
}
