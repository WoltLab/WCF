<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;

/**
 * Handles BBCodes displayed as buttons within the WYSIWYG editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class BBCodeHandler extends SingletonFactory {
	/**
	 * list of BBCodes allowed for usage
	 * @var	array<\wcf\data\bbcode\BBCode>
	 */
	protected $allowedBBCodes = array();
	
	/**
	 * list of BBCodes displayed as buttons
	 * @var	array<\wcf\data\bbcode\BBCode>
	 */
	protected $buttonBBCodes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
			if ($bbcode->showButton) {
				$this->buttonBBCodes[] = $bbcode;
			}
		}
	}
	
	/**
	 * Returns true if the BBCode with the given tag is available in the WYSIWYG editor.
	 * 
	 * @param	string		$bbCodeTag
	 * @return	boolean
	 */
	public function isAvailableBBCode($bbCodeTag) {
		$bbCode = BBCodeCache::getInstance()->getBBCodeByTag($bbCodeTag);
		if ($bbCode === null || $bbCode->isDisabled) {
			return false;
		}
		
		if (in_array('all', $this->allowedBBCodes)) {
			return true;
		}
		else if (in_array('none', $this->allowedBBCodes)) {
			return false;
		}
		
		return in_array($bbCodeTag, $this->allowedBBCodes);
	}
	
	/**
	 * Returns a list of BBCodes displayed as buttons.
	 * 
	 * @return	array<\wcf\data\bbcode\BBCode>
	 */
	public function getButtonBBCodes() {
		$buttons = array();
		foreach ($this->buttonBBCodes as $bbcode) {
			if ($this->isAvailableBBCode($bbcode->bbcodeTag)) {
				$buttons[] = $bbcode;
			}
		}
		
		return $buttons;
	}
	
	/**
	 * Sets the allowed BBCodes.
	 * 
	 * @param	array<string>
	 */
	public function setAllowedBBCodes(array $bbCodes) {
		$this->allowedBBCodes = $bbCodes;
	}
}
