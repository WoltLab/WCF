<?php

namespace wcf\system\html\toc;

/**
 * Represents an item of a table of contents with its children.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @implements \RecursiveIterator<int, self>
 */
class HtmlTocItem implements \Countable, \RecursiveIterator
{
    /**
     * @var HtmlTocItem[]
     */
    protected $children = [];

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var int
     */
    protected $depth = 0;

    /**
     * iterator position
     * @var int
     */
    private $position = 0;

    /**
     * @var ?HtmlTocItem
     */
    private $parent;

    public function __construct(int $level, string $id, string $title)
    {
        $this->level = $level;
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function getTitle(): string
    {
        return \preg_replace_callback('/^\s*(\d+)([\.):]|\s*-)\s*/', function ($matches) {
            // Strip of a enumeration prefix if the prefixed number matches
            // the current offset within the ToC.
            if ($this->getParent() && \intval($matches[1]) === ($this->getParent()->position + 1)) {
                return '';
            }

            return $matches[0];
        }, $this->title);
    }

    /**
     * @return void
     */
    public function setParent(HtmlTocItem $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return ?HtmlTocItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return void
     */
    public function addChild(self $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * @return void
     */
    public function setDepth(int $depth)
    {
        $this->depth = $depth;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * Returns the number of children.
     */
    #[\Override]
    public function count(): int
    {
        return \count($this->children);
    }

    /**
     * Returns true if this element is the last sibling.
     */
    public function isLastSibling(): bool
    {
        foreach ($this->getParent() as $key => $child) {
            if ($child === $this) {
                return $key === \count($this->getParent()) - 1;
            }
        }

        return false;
    }

    /**
     * Returns the number of open parent nodes.
     */
    public function getOpenParentNodes(): int
    {
        $element = $this;
        $i = 0;

        while ($element->getParent()->getParent() !== null && $element->isLastSibling()) {
            $i++;
            $element = $element->getParent();
        }

        return $i;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\Override]
    public function valid(): bool
    {
        return isset($this->children[$this->position]);
    }

    #[\Override]
    public function next(): void
    {
        $this->position++;
    }

    #[\Override]
    public function current(): self
    {
        return $this->children[$this->position];
    }

    #[\Override]
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return \RecursiveIterator<int, self>
     */
    #[\Override]
    public function getChildren(): \RecursiveIterator
    {
        return $this->children[$this->position];
    }

    #[\Override]
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    /**
     * @return \RecursiveIteratorIterator<$this>
     */
    public function getIterator()
    {
        return new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
    }
}
