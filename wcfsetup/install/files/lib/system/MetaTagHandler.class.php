<?php
namespace wcf\system;
use wcf\util\StringUtil;

/**
 * Handles meta tags.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message
 */
class MetaTagHandler extends SingletonFactory implements \Countable, \Iterator {
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of index to object relation
	 * @var	integer[]
	 */
	protected $indexToObject = [];
	
	/**
	 * list of meta tags
	 * @var	array
	 */
	protected $objects = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// set default tags
		if ($value = WCF::getLanguage()->get(META_DESCRIPTION)) {
			$this->addTag('description', 'description', $value);
		}
		if ($value = WCF::getLanguage()->get(META_KEYWORDS)) {
			$this->addTag('keywords', 'keywords', $value);
		}
		if ($value = WCF::getLanguage()->get(PAGE_TITLE)) {
			$this->addTag('og:site_name', 'og:site_name', $value, true);
		}	
	}
	
	/**
	 * Adds or replaces a meta tag.
	 * 
	 * @param	string		$identifier
	 * @param	string		$name
	 * @param	string		$value
	 * @param	boolean		$isProperty
	 */
	public function addTag($identifier, $name, $value, $isProperty = false) {
		if (!isset($this->objects[$identifier])) {
			$this->indexToObject[] = $identifier;
		}
		
		$this->objects[$identifier] = [
			'isProperty' => $isProperty,
			'name' => $name,
			'value' => $value
		];
		
		// replace description if Open Graph Protocol tag was given
		if ($name == 'og:description') {
			$this->addTag('description', 'description', $value);
		}
	}
	
	/**
	 * Removes a meta tag.
	 * 
	 * @param	string		$identifier
	 */
	public function removeTag($identifier) {
		if (isset($this->objects[$identifier])) {
			unset($this->objects[$identifier]);
			
			$this->indexToObject = array_keys($this->objects);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function count() {
		return count($this->objects);
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		$tag = $this->objects[$this->indexToObject[$this->index]];
		
		return '<meta ' . ($tag['isProperty'] ? 'property' : 'name') . '="' . $tag['name'] . '" content="' . StringUtil::encodeHTML($tag['value']) . '">';
	}
	
	/**
	 * CAUTION: This methods does not return the current iterator index,
	 * rather than the object key which maps to that index.
	 * 
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->indexToObject[$this->index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->indexToObject[$this->index]);
	}
}
