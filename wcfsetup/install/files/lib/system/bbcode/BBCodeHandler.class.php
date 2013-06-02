<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;

/**
 * Handles BBCodes displayed as buttons within the WYSIWYG editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class BBCodeHandler extends SingletonFactory {
	/**
	 * list of BBCodes displayed as buttons
	 * @var	array<wcf\data\bbcode\BBCode>
	 */
	protected $buttonBBCodes = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
			if ($bbcode->showButton) {
				$this->buttonBBCodes[] = $bbcode;
			}
		}
	}
	
	/**
	 * Returns a list of BBCodes displayed as buttons.
	 * 
	 * @return	array<wcf\data\bbcode\BBCode>
	 */
	public function getButtonBBCodes() {
		return $this->buttonBBCodes;
	}
}
