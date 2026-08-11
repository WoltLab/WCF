<?php

namespace wcf\util;

use wcf\system\exception\SystemException;

/**
 * Writes XML documents.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class XMLWriter
{
    /**
     * true, if a document is still processed
     * @var bool
     */
    protected $activeDocument = false;

    /**
     * number of open elements
     * @var int
     */
    protected $openElements = 0;

    /**
     * XMLWriter object
     * @var ?\XMLWriter
     */
    protected $xml;

    /**
     * Creates a new XML document.
     *
     * @param string[] $attributes
     * @return void
     */
    public function beginDocument(string $rootElement, string $namespace, string $schemaLocation, array $attributes = [])
    {
        if ($this->activeDocument) {
            throw new SystemException("Could not begin a new document unless the previous is finished");
        }

        if ($this->xml === null) {
            $this->xml = new \XMLWriter();
            $this->xml->openMemory();
            $this->xml->setIndent(true);
            $this->xml->setIndentString("\t");
        }

        $this->xml->startDocument('1.0', 'UTF-8');
        $this->startElement($rootElement);
        $attributes = \array_merge(
            [
                'xmlns' => $namespace,
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => $namespace . ' ' . $schemaLocation,
            ],
            // `xmlns`, `xmlns:xsi`, and `xsi:schemaLocation` are explicitly set
            // as first attributes in that order
            \array_filter($attributes, static function (string $attributeName) {
                return !\in_array($attributeName, ['xmlns', 'xmlns:xsi', 'xsi:schemaLocation'], true);
            }, \ARRAY_FILTER_USE_KEY)
        );
        $this->writeAttributes($attributes);

        $this->activeDocument = true;
    }

    /**
     * Returns the generated XML document or writes it to given filename. All open
     * elements will be automatically closed before flushing.
     *
     * @return string|int|void
     */
    public function endDocument(string $filename = '')
    {
        // mark document as done
        $this->activeDocument = false;

        // close all open tags
        while ($this->openElements) {
            $this->endElement();
        }

        if (empty($filename)) {
            // return XML as string
            return $this->xml->flush(true);
        } else {
            // write to file
            \file_put_contents($filename, $this->xml->flush(true));
        }
    }

    /**
     * Begins a new element.
     *
     * @param string[] $attributes
     * @return void
     */
    public function startElement(string $element, array $attributes = [])
    {
        $this->xml->startElement($element);
        $this->openElements++;

        if ($attributes !== []) {
            $this->writeAttributes($attributes);
        }
    }

    /**
     * Ends the last opened element.
     *
     * @return void
     */
    public function endElement()
    {
        if ($this->openElements !== 0) {
            $this->xml->endElement();
            $this->openElements--;
        }
    }

    /**
     * Writes an element directly.
     *
     * @param string[] $attributes
     *
     * @return void
     */
    public function writeElement(string $element, string $cdata, array $attributes = [], bool $writeAsCdata = true)
    {
        $this->startElement($element);

        // write attributes
        if ($attributes !== []) {
            $this->writeAttributes($attributes);
        }

        // content
        if ($cdata !== '') {
            if ($writeAsCdata) {
                $this->xml->writeCdata(StringUtil::escapeCDATA($cdata));
            } else {
                $this->xml->text($cdata);
            }
        }

        $this->endElement();
    }

    /**
     * Writes a comment.
     *
     * @return void
     * @since   5.2
     */
    public function writeComment(string $comment)
    {
        $this->xml->writeComment($comment);
    }

    /**
     * Writes an attribute to last opened element.
     *
     * @return void
     */
    public function writeAttribute(string $attribute, string $value)
    {
        $this->writeAttributes([$attribute => $value]);
    }

    /**
     * Writes a list of attributes to last opened element.
     *
     * @param string[] $attributes
     * @return void
     */
    public function writeAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->xml->writeAttribute($attribute, $value);
        }
    }
}
