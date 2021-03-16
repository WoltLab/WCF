<?php

namespace wcf\system\html\output\node;

use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\bbcode\KeywordHighlighter;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\IHtmlNode;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\html\toc\HtmlToc;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes a HTML string and renders the final output for display.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 * @method      HtmlOutputProcessor     getHtmlProcessor()
 */
class HtmlOutputNodeProcessor extends AbstractHtmlNodeProcessor
{
    /**
     * @inheritDoc
     */
    protected $nodeInterface = IHtmlOutputNode::class;

    /**
     * desired output type
     * @var string
     */
    protected $outputType = 'text/html';

    /**
     * enables keyword highlighting
     * @var bool
     */
    protected $keywordHighlighting = true;

    /**
     * @var string[]
     */
    protected $sourceBBCodes = [];

    /**
     * list of HTML tags that should have a trailing newline when converted
     * to text/plain content
     * @var string[]
     */
    public static $plainTextNewlineTags = ['br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'td', 'tr'];

    /**
     * HtmlOutputNodeProcessor constructor.
     */
    public function __construct()
    {
        $this->sourceBBCodes = HtmlBBCodeParser::getInstance()->getSourceBBCodes();
    }

    /**
     * Sets the desired output type.
     *
     * @param string $outputType desired output type
     */
    public function setOutputType($outputType)
    {
        $this->outputType = $outputType;
    }

    /**
     * Returns the current output type.
     *
     * @return  string
     */
    public function getOutputType()
    {
        return $this->outputType;
    }

    /**
     * @inheritDoc
     */
    public function process()
    {
        // fire event action
        EventHandler::getInstance()->fireAction($this, 'beforeProcess');

        // highlight keywords
        $this->highlightKeywords();

        $this->invokeHtmlNode(new HtmlOutputNodeWoltlabMetacode());

        $this->invokeHtmlNode(new HtmlOutputUnfurlUrlNode());

        // dynamic node handlers
        $this->invokeNodeHandlers('wcf\system\html\output\node\HtmlOutputNode', ['woltlab-metacode']);

        if ($this->getHtmlProcessor()->removeLinks) {
            $links = $this->getDocument()->getElementsByTagName('a');
            while ($links->length) {
                DOMUtil::removeNode($links->item(0), true);
            }
        }

        if ($this->outputType !== 'text/html') {
            // convert `<p>...</p>` into `...<br><br>`
            $paragraphs = $this->getDocument()->getElementsByTagName('p');
            while ($paragraphs->length) {
                $paragraph = $paragraphs->item(0);

                $isLastNode = true;
                $sibling = $paragraph;
                while ($sibling = $sibling->nextSibling) {
                    if ($sibling->nodeType === \XML_ELEMENT_NODE) {
                        if ($sibling->nodeName !== 'br') {
                            $isLastNode = false;
                            break;
                        }
                    } elseif ($sibling->nodeType === \XML_TEXT_NODE) {
                        if (StringUtil::trim($sibling->textContent) !== '') {
                            $isLastNode = false;
                            break;
                        }
                    }
                }

                if (!$isLastNode) {
                    // check if paragraph only contains <br>
                    if (StringUtil::trim($paragraph->textContent) === '') {
                        // get last element
                        $element = $paragraph->firstChild;
                        while ($element && $element->nodeType !== \XML_ELEMENT_NODE) {
                            $element = $element->nextSibling;
                        }

                        if ($paragraph->childNodes->length === 0 || ($element && $element->nodeName === 'br')) {
                            DOMUtil::removeNode($paragraph, true);
                            continue;
                        }
                    }

                    //for ($i = 0; $i < 2; $i++) {
                    $br = $this->getDocument()->createElement('br');
                    $paragraph->appendChild($br);
                    //}
                }

                DOMUtil::removeNode($paragraph, true);
            }

            if ($this->outputType === 'text/plain') {
                // remove all `\n` first
                $nodes = [];
                /** @var \DOMText $node */
                foreach ($this->getXPath()->query('//text()') as $node) {
                    if (\strpos($node->textContent, "\n") !== false) {
                        $nodes[] = $node;
                    }
                }
                foreach ($nodes as $node) {
                    $textNode = $this->getDocument()->createTextNode(\preg_replace('~\r?\n~', '', $node->textContent));
                    $node->parentNode->insertBefore($textNode, $node);
                    $node->parentNode->removeChild($node);
                }

                // insert a trailing newline for certain elements, such as `<br>` or `<li>`
                foreach (self::$plainTextNewlineTags as $tagName) {
                    $elements = $this->getDocument()->getElementsByTagName($tagName);
                    while ($elements->length) {
                        $element = $elements->item(0);

                        $newline = $this->getDocument()->createTextNode("\n");
                        $element->parentNode->insertBefore($newline, $element->nextSibling);
                        DOMUtil::removeNode($element, true);
                    }
                }

                // remove all other elements
                $elements = $this->getDocument()->getElementsByTagName('*');
                while ($elements->length) {
                    DOMUtil::removeNode($elements->item(0), true);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        $toc = '';
        if (MESSAGE_ENABLE_TOC && $this->getHtmlProcessor()->enableToc && $this->outputType === 'text/html') {
            $context = $this->getHtmlProcessor()->getContext();
            $idPrefix = \substr(\sha1($context['objectType'] . '-' . $context['objectID']), 0, 8);

            $toc = HtmlToc::forMessage($this->getDocument(), $idPrefix);
        }

        $html = $toc . parent::getHtml();

        if ($this->outputType === 'text/plain') {
            $html = StringUtil::trim($html);
            $html = StringUtil::decodeHTML($html);
        }

        return $html;
    }

    /**
     * Enables the keyword highlighting.
     *
     * @param bool $enable
     */
    public function enableKeywordHighlighting($enable = true)
    {
        $this->keywordHighlighting = $enable;
    }

    /**
     * Executes the keyword highlighting.
     */
    protected function highlightKeywords()
    {
        if (!$this->keywordHighlighting) {
            return;
        }
        if (!\count(KeywordHighlighter::getInstance()->getKeywords())) {
            return;
        }
        $keywordPattern = '(' . \implode('|', KeywordHighlighter::getInstance()->getKeywords()) . ')';

        $nodes = [];
        foreach ($this->getXPath()->query('//text()') as $node) {
            $value = StringUtil::trim($node->textContent);
            if (empty($value)) {
                // skip empty nodes
                continue;
            }

            // check if node is within a code element or link
            if ($this->hasCodeParent($node)) {
                continue;
            }

            $nodes[] = $node;
        }
        foreach ($nodes as $node) {
            $split = \preg_split('+' . $keywordPattern . '+i', $node->textContent, -1, \PREG_SPLIT_DELIM_CAPTURE);
            $count = \count($split);
            if ($count == 1) {
                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                // text
                if ($i % 2 == 0) {
                    $node->parentNode->insertBefore($node->ownerDocument->createTextNode($split[$i]), $node);
                } // match
                else {
                    /** @var \DOMElement $element */
                    $element = $node->ownerDocument->createElement('span');
                    $element->setAttribute('class', 'highlight');
                    $element->appendChild($node->ownerDocument->createTextNode($split[$i]));
                    $node->parentNode->insertBefore($element, $node);
                }
            }

            DOMUtil::removeNode($node);
        }
    }

    /**
     * Returns true if text node is inside a code element, suppressing any
     * auto-detection of content.
     *
     * @param \DOMText $text text node
     * @return      bool         true if text node is inside a code element
     */
    protected function hasCodeParent(\DOMText $text)
    {
        $parent = $text;
        /** @var \DOMElement $parent */
        while ($parent = $parent->parentNode) {
            $nodeName = $parent->nodeName;
            if ($nodeName === 'code' || $nodeName === 'kbd' || $nodeName === 'pre') {
                return true;
            } elseif (
                $nodeName === 'woltlab-metacode'
                && \in_array($parent->getAttribute('data-name'), $this->sourceBBCodes)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function invokeHtmlNode(IHtmlNode $htmlNode)
    {
        /** @var IHtmlOutputNode $htmlNode */
        $htmlNode->setOutputType($this->outputType);
        $htmlNode->setRemoveLinks($this->getHtmlProcessor()->removeLinks);

        parent::invokeHtmlNode($htmlNode);
    }
}
