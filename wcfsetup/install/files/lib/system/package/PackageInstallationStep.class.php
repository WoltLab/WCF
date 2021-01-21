<?php

namespace wcf\system\package;

use wcf\system\form\FormDocument;

/**
 * Represents step information within an installation node.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package
 */
class PackageInstallationStep
{
    /**
     * form document object
     * @var FormDocument
     */
    protected $document;

    /**
     * exception causing node splitting
     * @var null|SplitNodeException
     */
    protected $exception;

    /**
     * next installation node
     * @var string
     */
    protected $node = '';

    /**
     * indicates if current current node should be splitted
     * @var bool
     */
    protected $splitNode = false;

    /**
     * Sets next installation node.
     *
     * @param   string      $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * Returns next installation node.
     *
     * @return  string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Sets form document object.
     *
     * @param   FormDocument    $document
     */
    public function setDocument(FormDocument $document)
    {
        $this->document = $document;
    }

    /**
     * Returns the exception causing node splitting or `null` if the node has not been split
     * or if it was not split by an exception.
     *
     * @return  null|SplitNodeException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Returns HTML-representation of form document object.
     *
     * @return  string
     */
    public function getTemplate()
    {
        return $this->document->getHTML();
    }

    /**
     * Returns true if current step holds a form document object.
     *
     * @return  bool
     */
    public function hasDocument()
    {
        return $this->document !== null;
    }

    /**
     * Enforces node splitting.
     *
     * @param   null|SplitNodeException     $splitNodeException
     */
    public function setSplitNode(?SplitNodeException $splitNodeException = null)
    {
        $this->splitNode = true;
        $this->exception = $splitNodeException;
    }

    /**
     * Returns true if node should be splitted.
     *
     * @return  bool
     */
    public function splitNode()
    {
        return $this->splitNode;
    }
}
