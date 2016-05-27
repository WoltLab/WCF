<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;

/**
 * Handles BBCodes displayed as buttons within the WYSIWYG editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class BBCodeHandler extends SingletonFactory {
	/**
	 * list of BBCodes allowed for usage
	 * @var	BBCode[]
	 */
	protected $allowedBBCodes = [];
	
	/**
	 * list of BBCodes displayed as buttons
	 * @var	BBCode[]
	 */
	protected $buttonBBCodes = [];
	
	/**
	 * list of BBCodes which contain raw code (disabled BBCode parsing)
	 * @var	BBCode[]
	 */
	protected $sourceBBCodes = null;
	
	/**
	 * @inheritDoc
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
	 * Returns all bbcodes.
	 * 
	 * @return	BBCode[]
	 */
	public function getBBCodes() {
		return BBCodeCache::getInstance()->getBBCodes();
	}
	
	/**
	 * Returns a list of BBCodes displayed as buttons.
	 * 
	 * @return	BBCode[]
	 */
	public function getButtonBBCodes() {
		$buttons = [];
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
	 * @param	string[]	$bbCodes
	 */
	public function setAllowedBBCodes(array $bbCodes) {
		$this->allowedBBCodes = $bbCodes;
	}
	
	/**
	 * Returns a list of BBCodes which contain raw code (disabled BBCode parsing)
	 * 
	 * @return	BBCode[]
	 */
	public function getSourceBBCodes() {
		if (empty($this->allowedBBCodes)) {
			return [];
		}
		
		if ($this->sourceBBCodes === null) {
			$this->sourceBBCodes = [];
			
			foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
				if (!$bbcode->isSourceCode) {
					continue;
				}
				
				if ($this->isAvailableBBCode($bbcode->bbcodeTag)) {
					$this->sourceBBCodes[] = $bbcode;
				}
			}
		}
		
		return $this->sourceBBCodes;
	}
	
	/**
	 * Returns a list of known highlighters.
	 * 
	 * @return	string[]
	 */
	public function getHighlighters() {
		return BBCodeCache::getInstance()->getHighlighters();
	}
}
