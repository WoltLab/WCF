<?php
namespace wcf\data\style;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Represents the active user style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category	Community Framework
 *
 * @method	Style	getDecoratedObject()
 * @mixin	Style
 */
class ActiveStyle extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Style::class;
	
	/**
	 * Returns full path to specified image.
	 * 
	 * @param	string		$image
	 * @return	string
	 */
	public function getImage($image) {
		if (preg_match('~^https?://~', $image)) {
			return $image;
		}
		
		if ($this->imagePath && file_exists(WCF_DIR.$this->imagePath.$image)) {
			return WCF::getPath().$this->imagePath.$image;
		}
		
		return WCF::getPath().'images/'.$image;
	}
	
	/**
	 * Returns page logo.
	 * 
	 * @return	string
	 */
	public function getPageLogo() {
		if ($this->object->getVariable('pageLogo')) {
			return $this->getImage($this->object->getVariable('pageLogo'));
		}
		
		return '';
	}
}
