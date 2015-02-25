<?php
namespace wcf\system\message\quote;
use wcf\data\IMessage;

/**
 * Wrapper class for quoted messages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.quote
 * @category	Community Framework
 */
class QuotedMessage implements \Countable, \Iterator {
	/**
	 * list of full quotes for insertation
	 * @var	array<string>
	 */
	public $fullQuotes = array();
	
	/**
	 * quotable database object
	 * @var	\wcf\data\IQuotableDatabaseObject
	 */
	public $object = null;
	
	/**
	 * list of quotes (shortend)
	 * @var	array<string>
	 */
	public $quotes = array();
	
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
	 * Creates a new QuotedMessage object.
	 * 
	 * @param	\wcf\data\IMessage	$object
	 */
	public function __construct(IMessage $object) {
		$this->object = $object;
	}
	
	/**
	 * Adds a quote for this message.
	 * 
	 * @param	string		$quoteID
	 * @param	string		$quote
	 * @param	string		$fullQuote
	 */
	public function addQuote($quoteID, $quote, $fullQuote) {
		$this->fullQuotes[$quoteID] = $fullQuote;
		$this->quotes[$quoteID] = $quote;
		$this->indexToObject[] = $quoteID;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function __toString() {
		return $this->object->getTitle();
	}
	
	/**
	 * Forwards calls to the decorated object.
	 * 
	 * @param	string		$name
	 * @param	mixed		$value
	 * @return	mixed
	 */
	public function __call($name, $value) {
		return $this->object->$name();
	}
	
	/**
	 * Returns the full quote by quote id.
	 * 
	 * @param	string		$quoteID
	 * @return	string
	 */
	public function getFullQuote($quoteID) {
		if (isset($this->fullQuotes[$quoteID])) {
			return $this->fullQuotes[$quoteID];
		}
		
		return null;
	}
	
	/**
	 * Returns true if given quote id represents a full quote.
	 * 
	 * @param	string		$quoteID
	 * @return	boolean
	 */
	public function isFullQuote($quoteID) {
		if (isset($this->fullQuotes[$quoteID]) && $this->quotes[$quoteID] != $this->fullQuotes[$quoteID]) {
			// full quotes are parsed and differ from their original
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->quotes);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		$objectID = $this->indexToObject[$this->index];
		return $this->quotes[$objectID];
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
