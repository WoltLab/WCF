<?php
namespace wcf\data\bbcode;
use wcf\system\cache\builder\BBCodeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the bbcode cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode
 * @category	Community Framework
 */
class BBCodeCache extends SingletonFactory {
	/**
	 * cached bbcodes
	 * @var	array<\wcf\data\bbcode\BBCode>
	 */
	protected $cachedBBCodes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get bbcode cache
		$this->cachedBBCodes = BBCodeCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns all bbcodes.
	 * 
	 * @return	array<\wcf\data\bbcode\BBCode>
	 */
	public function getBBCodes() {
		return $this->cachedBBCodes;
	}
	
	/**
	 * Returns the BBCode with the given tag or null if no such BBCode exists.
	 * 
	 * @param	string		$tag
	 * @return	\wcf\data\bbcode\BBCode
	 */
	public function getBBCodeByTag($tag) {
		if (isset($this->cachedBBCodes[$tag])) {
			return $this->cachedBBCodes[$tag];
		}
		
		return null;
	}
	
	/**
	 * Returns all attributes of a bbcode.
	 * 
	 * @param	string		$tag
	 * @return	array<\wcf\data\bbcode\attribute\BBCodeAttribute>
	 */
	public function getBBCodeAttributes($tag) {
		return $this->cachedBBCodes[$tag]->getAttributes();
	}
}
