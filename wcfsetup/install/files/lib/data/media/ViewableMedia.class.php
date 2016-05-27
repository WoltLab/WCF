<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a viewable madia file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Media	getDecoratedObject()
 * @mixin	Media
 */
class ViewableMedia extends DatabaseObjectDecorator {
	/**
	 * @inheritdoc
	 */
	protected static $baseClass = Media::class;
	
	/**
	 * Returns a textual representation of the media file to be used in templates.
	 * 
	 * @return	string
	 */
	public function __toString() {
		if ($this->isImage) {
			return '<img src="'.$this->getLink().'" alt="'.StringUtil::encodeHTML($this->altText).'" />';
		}
		
		return '<a href="'.$this->getLink().'>'.$this->getTitle().'</a>';
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
				return '<img src="' . $this->getThumbnailLink('tiny') . '" alt="' . StringUtil::encodeHTML($this->altText) . '" style="width: ' . $size . 'px; height: ' . $size . 'px;" />';
			}
		}
		
		return '<span class="icon icon'.$size.' '.FileUtil::getIconClassByMimeType($this->fileType).'"></span>';
	}
	
	/**
	 * Returns a tag to display a certain thumbnail.
	 * 
	 * @param	string		$size		thumbnail size
	 * @return	string
	 * @throws	SystemException
	 */
	public function getThumbnailTag($size = '') {
		if (!isset(Media::getThumbnailSizes()[$size])) {
			throw new SystemException("Unknown thumbnail size '".$size."'");
		}
		
		return '<img src="'.$this->getThumbnailLink($size).'" alt="'.StringUtil::encodeHTML($this->altText).'" />';
	}
	
	/**
	 * Returns the viewable media file with the given id.
	 * 
	 * @param	integer		$mediaID
	 * @return	Media|null
	 */
	public static function getMedia($mediaID) {
		$mediaList = new ViewableMediaList();
		$mediaList->setObjectIDs([$mediaID]);
		$mediaList->readObjects();
		
		return $mediaList->search($mediaID);
	}
}
