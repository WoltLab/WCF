<?php

namespace wcf\system\html\input;

use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Reads a HTML string, applies filters and parses all nodes including bbcodes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputProcessor extends AbstractHtmlProcessor
{
    /**
     * list of embedded content grouped by type
     * @var array
     */
    protected $embeddedContent = [];

    /**
     * @var IHtmlInputFilter
     */
    protected $htmlInputFilter;

    /**
     * @var HtmlInputNodeProcessor
     */
    protected $htmlInputNodeProcessor;

    /**
     * skip the HTML filter during message reprocessing
     * @var bool
     */
    protected $skipFilter = false;

    /**
     * Processes the input html string.
     *
     * @param string $html html string
     * @param string $objectType object type identifier
     * @param int $objectID object id
     * @param bool $convertFromBBCode interpret input as bbcode
     */
    public function process($html, $objectType, $objectID = 0, $convertFromBBCode = false)
    {
        $this->reset();

        $this->setContext($objectType, $objectID);

        // enforce consistent newlines
        $html = StringUtil::trim(StringUtil::unifyNewlines($html));

        // check if this is true HTML or just a bbcode string
        if ($convertFromBBCode) {
            $html = $this->convertToHtml($html);
        }

        // filter HTML
        if (!$this->skipFilter) {
            $html = $this->getHtmlInputFilter()->apply($html);
        }

        // pre-parse HTML
        $this->getHtmlInputNodeProcessor()->load($this, $html);
        $this->getHtmlInputNodeProcessor()->process();
        $this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
    }

    /**
     * Processes a HTML string to provide the general DOM API. This method
     * does not perform any filtering or validation. You SHOULD NOT use this
     * to deal with HTML that has not been filtered previously.
     *
     * @param string $html html string
     */
    public function processIntermediate($html)
    {
        $this->getHtmlInputNodeProcessor()->load($this, $html);
    }

    /**
     * Reprocesses a message by transforming the message into an editor-like
     * state using plain bbcodes instead of metacode elements.
     *
     * @param string $html html string
     * @param string $objectType object type identifier
     * @param int $objectID object id
     * @since       3.1
     */
    public function reprocess($html, $objectType, $objectID)
    {
        $this->processIntermediate($html);

        // revert embedded bbcodes for re-evaluation
        $metacodes = DOMUtil::getElements($this->getHtmlInputNodeProcessor()->getDocument(), 'woltlab-metacode');
        foreach ($metacodes as $metacode) {
            $name = $metacode->getAttribute('data-name');
            $attributes = $this->getHtmlInputNodeProcessor()
                ->parseAttributes($metacode->getAttribute('data-attributes'));

            $bbcodeAttributes = '';
            foreach ($attributes as $attribute) {
                if (!empty($bbcodeAttributes)) {
                    $bbcodeAttributes .= ',';
                }

                if ($attribute === true) {
                    $bbcodeAttributes .= 'true';
                } elseif ($attribute === false) {
                    $bbcodeAttributes .= 'false';
                } elseif (\is_string($attribute) || \is_numeric($attribute)) {
                    $bbcodeAttributes .= "'" . \addcslashes($attribute, "'") . "'";
                } else {
                    // discard anything that is not string-like
                    $bbcodeAttributes .= "''";
                }
            }

            $text = $metacode->ownerDocument->createTextNode(
                '[' . $name . (!empty($bbcodeAttributes) ? '=' . $bbcodeAttributes : '') . ']'
            );
            $metacode->insertBefore($text, $metacode->firstChild);

            $text = $metacode->ownerDocument->createTextNode('[/' . $name . ']');
            $metacode->appendChild($text);

            DOMUtil::removeNode($metacode, true);
        }

        try {
            $this->skipFilter = true;
            $this->process($this->getHtml(), $objectType, $objectID, false);
        } finally {
            $this->skipFilter = false;
        }
    }

    /**
     * Processes only embedded content. This method should only be called when rebuilding
     * data where only embedded content is relevant, but no actual parsing is required.
     *
     * @param string $html html string
     * @param string $objectType object type identifier
     * @param int $objectID object id
     * @throws      \UnexpectedValueException
     */
    public function processEmbeddedContent($html, $objectType, $objectID)
    {
        if (!$objectID) {
            throw new \UnexpectedValueException("Object id parameter must be non-zero.");
        }

        $this->setContext($objectType, $objectID);

        $this->getHtmlInputNodeProcessor()->load($this, $html);
        $this->getHtmlInputNodeProcessor()->processEmbeddedContent();
        $this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
    }

    /**
     * Checks the input html for disallowed bbcodes and returns any matches.
     *
     * @return      string[]        list of matched disallowed bbcodes
     */
    public function validate()
    {
        return $this->getHtmlInputNodeProcessor()->validate();
    }

    /**
     * Enforces the maximum depth of nested quotes.
     *
     * @param int $depth
     */
    public function enforceQuoteDepth($depth, bool $isFullQuote = false)
    {
        $this->getHtmlInputNodeProcessor()->enforceQuoteDepth($depth, $isFullQuote);
    }

    /**
     * Returns the parsed HTML ready to store.
     *
     * @return      string  parsed html
     */
    public function getHtml()
    {
        return $this->getHtmlInputNodeProcessor()->getHtml();
    }

    /**
     * Returns the raw text content of current document.
     *
     * @return      string          raw text content
     */
    public function getTextContent()
    {
        return $this->getHtmlInputNodeProcessor()->getTextContent();
    }

    /**
     * Returns true if the message appears to be empty.
     *
     * @return      bool         true if message appears to be empty
     */
    public function appearsToBeEmpty()
    {
        return $this->getHtmlInputNodeProcessor()->appearsToBeEmpty();
    }

    /**
     * Returns the all embedded content data.
     *
     * @return array
     */
    public function getEmbeddedContent()
    {
        return $this->embeddedContent;
    }

    /**
     * @return HtmlInputNodeProcessor
     */
    public function getHtmlInputNodeProcessor()
    {
        if ($this->htmlInputNodeProcessor === null) {
            $this->htmlInputNodeProcessor = new HtmlInputNodeProcessor();
        }

        return $this->htmlInputNodeProcessor;
    }

    /**
     * Sets the new object id.
     *
     * @param int $objectID object id
     */
    public function setObjectID($objectID)
    {
        $this->context['objectID'] = $objectID;
    }

    /**
     * Resets internal states and discards references to objects.
     */
    protected function reset()
    {
        $this->embeddedContent = [];
        $this->htmlInputNodeProcessor = null;
    }

    /**
     * @return  IHtmlInputFilter
     */
    protected function getHtmlInputFilter()
    {
        if ($this->htmlInputFilter === null) {
            $this->htmlInputFilter = new MessageHtmlInputFilter();
        }

        return $this->htmlInputFilter;
    }

    /**
     * Converts bbcodes using newlines into valid HTML.
     *
     * @param string $html html string
     * @return      string          parsed html string
     */
    protected function convertToHtml($html)
    {
        // Do not use StringUtil::encodeHTML() / htmlspecialchars() or similar:
        //
        // This conversion is just used to convert an existing BBCode representation into a
        // valid HTML representation that preserves the semantics as closely as possible.
        //
        // For proper BBCode parsing we need to guarantee a specific representation of the
        // characters that mark up a BBCode (e.g. `[`, `]`, `,`, `'`), however the general
        // purpose HTML encoder's contract is just that the resulting string interpreted
        // as HTML will result in a TextNode with the original string as its textContent.
        //
        // For this reason we just encode the 4 characters that form the core of the HTML
        // syntax. This will be safe from a security perspective, as the resulting HTML
        // will still be processed by HTML Purifier which will filter out anything that
        // is questionable or malicious.
        $html = \str_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], $html);
        $html = \preg_replace('/\[attach=(\d+)\]/', "[attach=\\1,'none','2']", $html);
        $parts = \preg_split('~(\n+)~', $html, -1, \PREG_SPLIT_DELIM_CAPTURE);

        $openParagraph = false;
        $html = '';
        for ($i = 0, $length = \count($parts); $i < $length; $i++) {
            $part = $parts[$i];
            if (\strpos($part, "\n") !== false) {
                $newlines = \substr_count($part, "\n");
                if ($newlines === 1) {
                    $html .= '<br>';
                } else {
                    if ($openParagraph) {
                        $html .= '</p>';
                        $openParagraph = false;
                    }

                    // ignore one newline because a new paragraph with bbcodes is created
                    // using two subsequent newlines
                    $newlines--;
                    if ($newlines === 0) {
                        continue;
                    }

                    $html .= \str_repeat('<p><br></p>', $newlines);
                }
            } else {
                if (!$openParagraph) {
                    $html .= '<p>';
                }

                $html .= $part;
                $openParagraph = true;
            }
        }

        return $html . '</p>';
    }
}
