<?php

namespace wcf\system\search\acp;

use wcf\system\WCF;

/**
 * Represents a list of ACP search results.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPSearchResultList implements \Countable, \Iterator
{
    /**
     * current iterator index
     * @var int
     */
    protected int $index = 0;

    /**
     * result list title
     * @var string
     */
    protected string $title = '';

    /**
     * result list
     * @var ACPSearchResult[]
     */
    protected array $results = [];

    /**
     * Creates a new ACPSearchResultList.
     */
    public function __construct(string $title)
    {
        $this->title = WCF::getLanguage()->get('wcf.acp.search.provider.' . $title);
    }

    /**
     * Adds a result to the collection.
     */
    public function addResult(ACPSearchResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * Reduces the result collection by given count. If the count is higher
     * than the actual amount of results, the results will be cleared.
     */
    public function reduceResults(int $count): void
    {
        // more results than available should be wiped, just set it to 0
        if ($count >= \count($this->results)) {
            $this->results = [];
        } else {
            while ($count > 0) {
                \array_pop($this->results);
                $count--;
            }
        }

        // rewind index to prevent bad offsets
        $this->rewind();
    }

    /**
     * Reduces the result collection to specified size.
     */
    public function reduceResultsTo(int $size): void
    {
        $count = \count($this->results);

        if ($size && ($count > $size)) {
            $reduceBy = $count - $size;
            $this->reduceResults($reduceBy);
        }
    }

    /**
     * Sorts results by title.
     */
    public function sort(): void
    {
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \usort(
            $this->results,
            static fn (ACPSearchResult $a, ACPSearchResult $b) => $collator->compare($a->getTitle(), $b->getTitle())
        );
    }

    /**
     * Returns the result list title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->results);
    }

    /**
     * @inheritDoc
     */
    public function current(): ACPSearchResult
    {
        return $this->results[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->results[$this->index]);
    }
}
