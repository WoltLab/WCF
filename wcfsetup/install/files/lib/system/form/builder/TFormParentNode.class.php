<?php

namespace wcf\system\form\builder;

use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IImmutableFormField;

/**
 * Provides default implementations of `IFormParentNode` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @mixin   IFormParentNode
 */
trait TFormParentNode
{
    /**
     * child nodes of this node
     * @var IFormChildNode[]
     */
    protected $children = [];

    /**
     * current iterator index
     * @var int
     */
    protected $index = 0;

    /**
     * Appends the given node to this node and returns this node.
     *
     * @return  $this
     *
     * @throws  \BadMethodCallException     if method is called with more than one parameter (might be mistakenly used instead of `appendChildren()`)
     */
    public function appendChild(IFormChildNode $child): static
    {
        if (\func_num_args() > 1) {
            throw new \BadMethodCallException("'" . IFormParentNode::class . "::appendChild()' only supports one argument. Use '" . IFormParentNode::class . "::appendChildren()' to append multiple children at once.");
        }

        $this->children[] = $child;

        $child->parent($this);

        return $this;
    }

    /**
     * Appends the given children to this node and returns this node.
     *
     * @param IFormChildNode[] $children appended children
     * @return  $this
     */
    public function appendChildren(array $children): static
    {
        foreach ($children as $child) {
            $this->appendChild($child);
        }

        return $this;
    }

    /**
     * Returns `true` if this node (or any of the child nodes) contains the node
     * with the given id and returns `false` otherwise.
     */
    public function contains(string $nodeId): bool
    {
        static::validateId($nodeId);

        foreach ($this->children() as $child) {
            if ($child->getId() === $nodeId) {
                return true;
            }

            if ($child instanceof IFormParentNode && $child->contains($nodeId) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all child nodes of this node.
     *
     * @return  IFormChildNode[]
     */
    public function children(): array
    {
        return $this->children;
    }

    /**
     * Cleans up after the form data has been saved and the form is not used anymore.
     * This method has to support being called multiple times.
     *
     * This method is not meant to empty the value of input fields.
     *
     * @return  $this
     */
    public function cleanup(): static
    {
        foreach ($this->children as $index => $child) {
            $child->cleanup();

            unset($this->children[$index]);
        }

        return $this;
    }

    /**
     * Returns the number of direct children of this node.
     */
    public function count(): int
    {
        return \count($this->children);
    }

    /**
     * Returns the current child node during the iteration.
     */
    public function current(): IFormChildNode
    {
        return $this->children[$this->index];
    }

    /**
     * Returns an iterator for the current child node.
     */
    public function getChildren(): ?IFormParentNode
    {
        $node = $this->children[$this->index];
        if ($node instanceof IFormParentNode) {
            return $node;
        }

        // signal leafs to \RecursiveIteratorIterator so that leaves do no have to
        // implement \RecursiveIterator; exception will be ignored because of the
        // constructor flag `\RecursiveIteratorIterator::CATCH_GET_CHILD`
        throw new \BadMethodCallException();
    }

    /**
     * Returns a recursive iterator for this node.
     *
     * @return  \RecursiveIteratorIterator  recursive iterator for this node
     */
    public function getIterator()
    {
        return new \RecursiveIteratorIterator(
            $this,
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );
    }

    /**
     * Returns the node with the given id or `null` if no such node exists.
     *
     * All descendants, not only the direct child nodes, are checked to find the
     * requested node.
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public function getNodeById(string $nodeId): ?IFormNode
    {
        static::validateId($nodeId);

        foreach ($this->children() as $child) {
            if ($child->getId() === $nodeId) {
                return $child;
            }

            if ($child instanceof IFormParentNode) {
                $node = $child->getNodeById($nodeId);
                if ($node !== null) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * Returns `true` if the node as any children and return `false` otherwise.
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Returns `true` if this node or any of its children has a validation error and
     * return `false` otherwise.
     */
    public function hasValidationErrors(): bool
    {
        foreach ($this->children() as $child) {
            if ($child instanceof IFormField) {
                if (!empty($child->getValidationErrors())) {
                    return true;
                }
            } elseif ($child instanceof IFormParentNode) {
                if ($child->hasValidationErrors()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Inserts the given node after the node with the given id and returns this node.
     *
     * @param $referenceNodeId id of the node after which the given node is inserted
     * @return  $this
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertAfter(IFormChildNode $child, string $referenceNodeId): static
    {
        $didInsertNode = false;
        foreach ($this->children() as $index => $existingChild) {
            if ($existingChild->getId() === $referenceNodeId) {
                \array_splice($this->children, $index + 1, 0, [$child]);

                $child->parent($this);

                $didInsertNode = true;
                break;
            }
        }

        if (!$didInsertNode) {
            throw new \InvalidArgumentException(
                "Unknown child node with id '{$referenceNodeId}' for node '{$this->getId()}'."
            );
        }

        return $this;
    }

    /**
     * Inserts the given node before the node with the given id and returns this node.
     *
     * @param $referenceNodeId id of the node before which the given node is inserted
     * @return  $this
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertBefore(IFormChildNode $child, string $referenceNodeId): static
    {
        $didInsertNode = false;
        foreach ($this->children() as $index => $existingChild) {
            if ($existingChild->getId() === $referenceNodeId) {
                \array_splice($this->children, $index, 0, [$child]);

                $child->parent($this);

                $didInsertNode = true;
                break;
            }
        }

        if (!$didInsertNode) {
            throw new \InvalidArgumentException(
                "Unknown child node with id '{$referenceNodeId}' for node '{$this->getId()}'."
            );
        }

        return $this;
    }

    /**
     * Return the key of the current element during the iteration.
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Moves the iterator internally forward to next element.
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Reads the value of this node and its children from request data and
     * return this field.
     *
     * @return  $this
     */
    public function readValues(): static
    {
        if ($this->isAvailable()) {
            foreach ($this->children() as $child) {
                if ($child instanceof IFormParentNode) {
                    $child->readValues();
                } elseif (
                    $child instanceof IFormField
                    && $child->isAvailable()
                    && (!($child instanceof IImmutableFormField) || !$child->isImmutable())
                ) {
                    $child->readValue();
                }
            }
        }

        return $this;
    }

    /**
     * Rewind the iterator to the first element.
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Returns `true` if the current position during the iteration is valid and returns
     * `false` otherwise.
     */
    public function valid(): bool
    {
        return isset($this->children[$this->index]);
    }

    /**
     * Validates the node.
     *
     * Note: A `IFormParentNode` object may only return `true` if all of its child
     * nodes are valid. A `IFormField` object is valid if its value is valid.
     */
    public function validate()
    {
        if ($this->isAvailable() && $this->checkDependencies()) {
            foreach ($this->children() as $child) {
                // call `checkDependencies()` on form fields here so that their validate
                // method does not have to do it
                if ($child instanceof IFormField && (!$child->isAvailable() || !$child->checkDependencies())) {
                    continue;
                }

                $child->validate();

                if ($child instanceof IFormField && empty($child->getValidationErrors())) {
                    foreach ($child->getValidators() as $validator) {
                        $validator($child);

                        if (!empty($child->getValidationErrors())) {
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if the given node can be added as a child to this node.
     *
     * @throws  \InvalidArgumentException       if given node cannot be added as a child
     */
    public function validateChild(IFormChildNode $child): void
    {
        // does nothing
    }
}
