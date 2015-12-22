<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectDecorator;
use wcf\util\StringUtil;
use wcf\util\FileUtil;

/**
 * Represents a viewable madia file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 */
class ViewableMedia extends DatabaseObjectDecorator {
	/**
	 * @inheritdoc
	 */
	protected static $baseClass = Media::class;
	
	/**
	 * Returns a tag to display the media element.
	 * 
	 * @param	string		$size
	 * @return	string
	 */
	public function getElementTag($size) {
		// todo: validate $size
		if ($this->isImage && $this->tinyThumbnailType) {
			return '<img src="'.$this->getThumbnailLink('tiny').'" alt="" style="width: '.$size.'px; height: '.$size.'px;" />';
		}
		
		return '<span class="icon icon'.$size.' '.FileUtil::getIconClassByMimeType($this->fileType).'"></span>';
	}
}
