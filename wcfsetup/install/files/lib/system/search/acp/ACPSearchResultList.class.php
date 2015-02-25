<?php
namespace wcf\system\search\acp;
use wcf\system\WCF;

/**
 * Represents a list of ACP search results.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class ACPSearchResultList implements \Countable, \Iterator {
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * result list title
	 * @var	string
	 */
	protected $title = '';
	
	/**
	 * result list
	 * @var	array<\wcf\system\search\acp\ACPSearchResult>
	 */
	protected $results = array();
	
	/**
	 * Creates a new ACPSearchResultList.
	 * 
	 * @param	string		$title
	 */
	public function __construct($title) {
		$this->title = WCF::getLanguage()->get('wcf.acp.search.provider.'.$title);
	}
	
	/**
	 * Adds a result to the collection.
	 * 
	 * @param	\wcf\system\search\acp\ACPSearchResult	$result
	 */
	public function addResult(ACPSearchResult $result) {
		$this->results[] = $result;
	}
	
	/**
	 * Reduces the result collection by given count. If the count is higher
	 * than the actual amount of results, the results will be cleared.
	 * 
	 * @param	integer		$count
	 */
	public function reduceResults($count) {
		// more results than available should be whiped, just set it to 0
		if ($count >= count($this->results)) {
			$this->results = array();
		}
		else {
			while ($count > 0) {
				array_pop($this->results);
				$count--;
			}
		}
		
		// rewind index to prevent bad offsets
		$this->rewind();
	}
	
	/**
	 * Reduces the result collection to specified size.
	 * 
	 * @param	integer		$size
	 */
	public function reduceResultsTo($size) {
		$count = count($this->results);
		
		if ($size && ($count > $size)) {
			$reduceBy = $count - $size;
			$this->reduceResults($reduceBy);
		}
	}
	
	/**
	 * Sorts results by title.
	 */
	public function sort() {
		usort($this->results, function($a, $b) {
			return strcmp($a->getTitle(), $b->getTitle());
		});
	}
	
	/**
	 * Returns the result list title.
	 * 
	 * @return	string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @see	\wcf\system\search\acp\ACPSearchResultList::getTitle()
	 */
	public function __toString() {
		return $this->title;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->results);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->results[$this->index];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->index;
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
		return isset($this->results[$this->index]);
	}
}
