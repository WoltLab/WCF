<?php

namespace wcf\system\package;

use wcf\system\form\FormDocument;

/**
 * Represents step information within an installation node.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package
 */
final class PackageInstallationStep
{
    private FormDocument $document;

    /**
     * exception causing node splitting
     */
    private ?SplitNodeException $exception = null;

    /**
     * next installation node
     */
    private string $node = '';

    /**
     * indicates if current current node should be splitted
     */
    private bool $splitNode = false;

    /**
     * Sets next installation node.
     */
    public function setNode(string $node): void
    {
        $this->node = $node;
    }

    /**
     * Returns next installation node.
     */
    public function getNode(): string
    {
        return $this->node;
    }

    /**
     * Sets form document object.
     */
    public function setDocument(FormDocument $document): void
    {
        $this->document = $document;
    }

    /**
     * Returns the exception causing node splitting or `null` if the node has not been split
     * or if it was not split by an exception.
     */
    public function getException(): ?SplitNodeException
    {
        return $this->exception;
    }

    /**
     * Returns HTML-representation of form document object.
     */
    public function getTemplate(): string
    {
        return $this->document->getHTML();
    }

    /**
     * Returns true if current step holds a form document object.
     */
    public function hasDocument(): bool
    {
        return isset($this->document);
    }

    /**
     * Enforces node splitting.
     */
    public function setSplitNode(?SplitNodeException $splitNodeException = null): void
    {
        $this->splitNode = true;
        $this->exception = $splitNodeException;
    }

    /**
     * Returns true if node should be splitted.
     */
    public function splitNode(): bool
    {
        return $this->splitNode;
    }
}
