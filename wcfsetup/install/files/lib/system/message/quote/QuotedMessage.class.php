<?php

namespace wcf\system\message\quote;

use wcf\data\IMessage;

/**
 * Wrapper class for quoted messages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Message\Quote
 *
 * Note: We cannot use mixin here as that causes errors about methods not being implemented.
 * @method  string      getExcerpt($maxLength = 255)
 * @method  string      getFormattedMessage()
 * @method  string      getLink()
 * @method  string      getMessage()
 * @method  int     getTime()
 * @method  string      getTitle()
 * @method  int     getUserID()
 * @method  string      getUsername()
 * @method  bool        isVisible()
 */
class QuotedMessage implements \Countable, \Iterator
{
    /**
     * list of full quotes for insertation
     * @var string[]
     */
    public $fullQuotes = [];

    /**
     * quotable database object
     * @var IMessage
     */
    public $object;

    /**
     * overrides full quote flag
     * @var bool[]
     */
    public $overrideIsFullQuote = [];

    /**
     * list of quotes (shortened)
     * @var string[]
     */
    public $quotes = [];

    /**
     * current iterator index
     * @var int
     */
    protected $index = 0;

    /**
     * list of index to object relation
     * @var int[]
     */
    protected $indexToObject;

    /**
     * Creates a new QuotedMessage object.
     *
     * @param IMessage $object
     */
    public function __construct(IMessage $object)
    {
        $this->object = $object;
    }

    /**
     * Adds a quote for this message.
     *
     * @param string $quoteID
     * @param string $quote
     * @param string $fullQuote
     */
    public function addQuote($quoteID, $quote, $fullQuote)
    {
        $this->fullQuotes[$quoteID] = $fullQuote;
        $this->quotes[$quoteID] = $quote;
        $this->indexToObject[] = $quoteID;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->object->getTitle();
    }

    /**
     * Forwards calls to the decorated object.
     *
     * @param string $name
     * @param mixed $value
     * @return  mixed
     */
    public function __call($name, $value)
    {
        return $this->object->{$name}();
    }

    /**
     * Overrides the full quote flag.
     *
     * @param string $quoteID
     * @param bool $overrideIsFullQuote
     */
    public function setOverrideIsFullQuote($quoteID, $overrideIsFullQuote)
    {
        $this->overrideIsFullQuote[$quoteID] = $overrideIsFullQuote;
    }

    /**
     * Returns the list of quote ids for this message.
     *
     * @return      string[]
     */
    public function getQuoteIDs()
    {
        return \array_keys($this->quotes);
    }

    /**
     * Returns the full quote by quote id.
     *
     * @param string $quoteID
     * @return  string|null
     */
    public function getFullQuote($quoteID)
    {
        return $this->fullQuotes[$quoteID] ?? null;
    }

    /**
     * Returns true if given quote id represents a full quote.
     *
     * @param string $quoteID
     * @return  bool
     */
    public function isFullQuote($quoteID)
    {
        if (isset($this->overrideIsFullQuote[$quoteID])) {
            return $this->overrideIsFullQuote[$quoteID];
        }

        if (isset($this->fullQuotes[$quoteID]) && $this->quotes[$quoteID] != $this->fullQuotes[$quoteID]) {
            // full quotes are parsed and differ from their original
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->quotes);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $objectID = $this->indexToObject[$this->index];

        return $this->quotes[$objectID];
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * rather than the object key which maps to that index.
     *
     * @see \Iterator::key()
     */
    public function key()
    {
        return $this->indexToObject[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->indexToObject[$this->index]);
    }
}
