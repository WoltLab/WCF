<?php

namespace wcf\data;

use wcf\util\ClassUtil;

/**
 * Default implementation of `IObjectTreeNode`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TObjectTreeNode
{
    /**
     * child nodes
     * @var static[]
     */
    protected $children = [];

    /**
     * current iterator key
     * @var int
     */
    protected $index = 0;

    /**
     * parent node object
     * @var static
     */
    protected $parentNode;

    /**
     * Adds the given node as child node and sets the child node's parent node to this node.
     *
     * @param IObjectTreeNode $child added child node
     * @throws  \InvalidArgumentException       if given object is no (deocrated) instance of this class
     */
    public function addChild(IObjectTreeNode $child)
    {
        if (!($child instanceof $this) && !ClassUtil::isDecoratedInstanceOf($child, static::class)) {
            throw new \InvalidArgumentException("Child has to be a (decorated) instance of '" . static::class . "', but instance of '" . \get_class($child) . "' given.");
        }

        $child->setParentNode($this);

        $this->children[] = $child;
    }

    /**
     * Returns the number of child nodes.
     */
    public function count(): int
    {
        return \count($this->children);
    }

    /**
     * Return the currently iterated child node.
     */
    public function current(): static
    {
        return $this->children[$this->index];
    }

    /**
     * Returns an iterator for the currently iterated child node by returning the node itself.
     */
    public function getChildren(): static
    {
        return $this->children[$this->index];
    }

    /**
     * Returns the depth of the node within the tree.
     *
     * The minimum depth is `1`.
     */
    public function getDepth(): int
    {
        $element = $this;
        $depth = 1;

        while ($element->parentNode->parentNode !== null) {
            $depth++;
            $element = $element->parentNode;
        }

        return $depth;
    }

    /**
     * Returns the number of open parent nodes.
     */
    public function getOpenParentNodes(): int
    {
        $element = $this;
        $i = 0;

        while ($element->parentNode->parentNode !== null && $element->isLastSibling()) {
            $i++;
            $element = $element->parentNode;
        }

        return $i;
    }

    /**
     * Retruns the parent node of this node.
     */
    public function getParentNode(): ?self
    {
        return $this->parentNode;
    }

    /**
     * Returns `true` if the node as any children and return `false` otherwise.
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Return the key of the currently iterated child node.
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Returns `true` if this node is the last sibling and `false` otherwise.
     */
    public function isLastSibling(): bool
    {
        foreach ($this->parentNode as $key => $child) {
            if ($child === $this) {
                return $key === \count($this->parentNode) - 1;
            }
        }

        throw new \LogicException("Unreachable");
    }

    /**
     * Moves the iteration forward to next child node.
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Rewind the iteration to the first child node.
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Sets the parent node of this node.
     *
     * @throws  \InvalidArgumentException       if given object is no (deocrated) instance of this class
     */
    public function setParentNode(IObjectTreeNode $parentNode)
    {
        if (!($parentNode instanceof $this) && !ClassUtil::isDecoratedInstanceOf($parentNode, static::class)) {
            throw new \InvalidArgumentException("Parent has to be a (decorated) instance of '" . static::class . "', but instance of '" . \get_class($parentNode) . "' given.");
        }

        $this->parentNode = $parentNode;
    }

    /**
     * Returns `true` if current iteration position is valid and `false` otherwise.
     */
    public function valid(): bool
    {
        return isset($this->children[$this->index]);
    }
}
