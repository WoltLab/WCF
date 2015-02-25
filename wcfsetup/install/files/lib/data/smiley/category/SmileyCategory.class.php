<?php
namespace wcf\data\smiley\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\smiley\SmileyCache;
use wcf\data\ITraversableObject;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a smiley category.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.smiley.category
 * @category	Community Framework
 */
class SmileyCategory extends AbstractDecoratedCategory implements \Countable, ITraversableObject {
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
	 * list of assigned smilies
	 * @var	array<\wcf\data\smiley\Smiley>
	 */
	public $smilies = null;
	
	/**
	 * Loads associated smilies from cache.
	 */
	public function loadSmilies() {
		if ($this->smilies === null) {
			$this->smilies = SmileyCache::getInstance()->getCategorySmilies($this->categoryID ?: null);
			$this->indexToObject = array_keys($this->smilies);
		}
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->smilies);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		$objectID = $this->indexToObject[$this->index];
		return $this->smilies[$objectID];
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
	
	/**
	 * @see	\SeekableIterator::seek()
	 */
	public function seek($index) {
		$this->index = $index;
		
		if (!$this->valid()) {
			throw new \OutOfBoundsException();
		}
	}
	
	/**
	 * @see	\wcf\data\ITraversableObject::seekTo()
	 */
	public function seekTo($objectID) {
		$this->index = array_search($objectID, $this->indexToObject);
		
		if ($this->index === false) {
			throw new SystemException("object id '".$objectID."' is invalid");
		}
	}
	
	/**
	 * @see	\wcf\data\ITraversableObject::search()
	 */
	public function search($objectID) {
		try {
			$this->seekTo($objectID);
			return $this->current();
		}
		catch (SystemException $e) {
			return null;
		}
	}
	
	/**
	 * Returns the category's name.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return WCF::getLanguage()->get($this->title);
	}
}
