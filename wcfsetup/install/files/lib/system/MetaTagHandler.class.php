<?php
namespace wcf\system;
use wcf\util\StringUtil;

/**
 * Handles meta tags.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message
 * @category	Community Framework
 */
class MetaTagHandler extends SingletonFactory implements \Countable, \Iterator {
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of index to object relation
	 * @var	array<integer>
	 */
	protected $indexToObject = null;
	
	/**
	 * list of meta tags
	 * @var	array
	 */
	protected $objects = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// set default tags
		$this->addTag('description', 'description', WCF::getLanguage()->get(META_DESCRIPTION));
		$this->addTag('keywords', 'keywords', WCF::getLanguage()->get(META_KEYWORDS));
		$this->addTag('og:site_name', 'og:site_name', WCF::getLanguage()->get(PAGE_TITLE), true);
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
		
		$this->objects[$identifier] = array(
			'isProperty' => $isProperty,
			'name' => $name,
			'value' => $value
		);
		
		// replace description if Open Graph Protocol tag was given
		if ($name == 'og:description') {
			$this->objects['description']['value'] = $value;
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
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->objects);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		$tag = $this->objects[$this->indexToObject[$this->index]];
		
		return '<meta ' . ($tag['isProperty'] ? 'property' : 'name') . '="' . $tag['name'] . '" content="' . StringUtil::encodeHTML($tag['value']) . '" />';
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
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->indexToObject[$this->index]);
	}
}
