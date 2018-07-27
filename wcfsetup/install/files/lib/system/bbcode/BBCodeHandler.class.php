<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;

/**
 * Handles BBCodes displayed as buttons within the WYSIWYG editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class BBCodeHandler extends SingletonFactory {
	/**
	 * list of BBCodes displayed as buttons
	 * @var	BBCode[]
	 */
	protected $buttonBBCodes = [];
	
	/**
	 * list of BBCodes disallowed for usage
	 * @var	BBCode[]
	 */
	protected $disallowedBBCodes = [];
	
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
		return !in_array($bbCodeTag, $this->disallowedBBCodes);
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
	 * @param       boolean         $excludeCoreBBCodes     do not return bbcodes that are available by default
	 * @return	BBCode[]
	 */
	public function getButtonBBCodes($excludeCoreBBCodes = false) {
		$buttons = [];
		$coreBBCodes = ['align', 'b', 'color', 'i', 'img', 'list', 's', 'size', 'sub', 'sup', 'quote', 'table', 'u', 'url'];
		foreach ($this->buttonBBCodes as $bbcode) {
			if ($excludeCoreBBCodes && in_array($bbcode->bbcodeTag, $coreBBCodes)) {
				continue;
			}
			
			if ($this->isAvailableBBCode($bbcode->bbcodeTag)) {
				$buttons[] = $bbcode;
			}
		}
		
		return $buttons;
	}
	
	/**
	 * @param string[] $bbcodes
	 * @deprecated 3.0 - please use setDisallowedBBCodes() instead
	 */
	public function setAllowedBBCodes(array $bbcodes) {
		throw new \RuntimeException("setAllowedBBCodes() is no longer supported, please use setDisallowedBBCodes() instead.");
	}
	
	/**
	 * Sets the allowed BBCodes.
	 * 
	 * @param	string[]	$bbCodes
	 */
	public function setDisallowedBBCodes(array $bbCodes) {
		$this->disallowedBBCodes = $bbCodes;
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
