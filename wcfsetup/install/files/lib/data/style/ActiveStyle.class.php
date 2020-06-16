<?php
namespace wcf\data\style;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Represents the active user style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style
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
		if (preg_match('~^(https?)://~', $image, $matches)) {
			// rewrite protocol
			if ($matches[1] === 'http' && RouteHandler::secureConnection()) {
				return 'https' . mb_substr($image, 4);
			}
			
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
		if ($this->getDecoratedObject()->getVariable('pageLogo')) {
			return $this->getImage($this->getDecoratedObject()->getVariable('pageLogo'));
		}
		
		return WCF::getPath() . 'images/default-logo.png';
	}
	
	/**
	 * Returns mobile page logo.
	 *
	 * @return	string
	 */
	public function getPageLogoMobile() {
		if ($this->getDecoratedObject()->getVariable('pageLogoMobile')) {
			return $this->getImage($this->getDecoratedObject()->getVariable('pageLogoMobile'));
		}
		
		return WCF::getPath() . 'images/default-logo-small.png';
	}
}
