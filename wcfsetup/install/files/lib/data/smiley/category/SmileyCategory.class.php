<?php
namespace wcf\data\smiley\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\data\ITraversableObject;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a smiley category.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Smiley\Category
 */
class SmileyCategory extends AbstractDecoratedCategory implements \Countable, ITraversableObject {
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of index to object relation
	 * @var	integer[]
	 */
	protected $indexToObject = null;
	
	/**
	 * list of assigned smilies
	 * @var	Smiley[]
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
	 * @inheritDoc
	 */
	public function count() {
		return count($this->smilies);
	}
	
	/**
	 * @inheritDoc
	 * @return	Smiley
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
	
	/**
	 * @inheritDoc
	 */
	public function seek($index) {
		$this->index = $index;
		
		if (!$this->valid()) {
			throw new \OutOfBoundsException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function seekTo($objectID) {
		$this->index = array_search($objectID, $this->indexToObject);
		
		if ($this->index === false) {
			throw new SystemException("object id '".$objectID."' is invalid");
		}
	}
	
	/**
	 * @inheritDoc
	 * @return	Smiley|null
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
