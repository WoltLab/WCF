<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectDecorator;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a viewable madia file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 * 
 * @method	Media	getDecoratedObject()
 * @mixin	Media
 * @property-read	string|null	$title
 * @property-read	string|null	$description
 * @property-read	string|null	$altText
 */
class ViewableMedia extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Media::class;
	
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
		
		return '<span class="icon icon'.$size.' '.FileUtil::getIconClassByMimeType($this->fileType).'"></span>';
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
