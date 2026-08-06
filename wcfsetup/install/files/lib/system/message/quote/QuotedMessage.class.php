<?php

namespace wcf\system\message\quote;

use wcf\data\IMessage;

/**
 * Wrapper class for quoted messages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
 * @implements \Iterator<string>
 *
 * @deprecated 6.2
 */
class QuotedMessage implements \Countable, \Iterator, \Stringable
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
     * @var list<string>|null
     */
    protected $indexToObject;

    public function __construct(IMessage $object)
    {
        $this->object = $object;
    }

    /**
     * Adds a quote for this message.
     *
     * @return void
     */
    public function addQuote(string $quoteID, string $quote, string $fullQuote)
    {
        $this->fullQuotes[$quoteID] = $fullQuote;
        $this->quotes[$quoteID] = $quote;
        $this->indexToObject[] = $quoteID;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->object->getTitle();
    }

    /**
     * Forwards calls to the decorated object.
     *
     * @param mixed[] $arguments
     * @return  mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->object->{$name}();
    }

    /**
     * Overrides the full quote flag.
     *
     * @return void
     */
    public function setOverrideIsFullQuote(string $quoteID, bool $overrideIsFullQuote)
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
     * @return  string|null
     */
    public function getFullQuote(string $quoteID)
    {
        return $this->fullQuotes[$quoteID] ?? null;
    }

    /**
     * Returns true if given quote id represents a full quote.
     *
     * @return  bool
     */
    public function isFullQuote(string $quoteID)
    {
        if (isset($this->overrideIsFullQuote[$quoteID])) {
            return $this->overrideIsFullQuote[$quoteID];
        }

        if (isset($this->fullQuotes[$quoteID]) && $this->quotes[$quoteID] !== $this->fullQuotes[$quoteID]) {
            // full quotes are parsed and differ from their original
            return true;
        }

        return false;
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->quotes);
    }

    #[\Override]
    public function current(): string
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
    #[\Override]
    public function key(): string
    {
        return $this->indexToObject[$this->index];
    }

    #[\Override]
    public function next(): void
    {
        $this->index++;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    #[\Override]
    public function valid(): bool
    {
        return isset($this->indexToObject[$this->index]);
    }
}
