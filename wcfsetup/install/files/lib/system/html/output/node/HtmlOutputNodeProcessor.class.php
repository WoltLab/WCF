<?php

namespace wcf\system\html\output\node;

use wcf\system\bbcode\BBCodeParser;
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
        $this->sourceBBCodes = BBCodeParser::getInstance()->getSourceBBCodes();
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
        EventHandler::getInstance()->fireAction($this, 'beforeProcess');

        $this->removeTextFormatting();

        $this->highlightKeywords();

        $this->invokeHtmlNode(new HtmlOutputNodeWoltlabMetacode());

        if (MODULE_URL_UNFURLING) {
            $this->invokeHtmlNode(new HtmlOutputUnfurlUrlNode());
        }

        (new HtmlOutputNodeNormalizer())->normalize($this->getXPath());

        // dynamic node handlers
        $this->invokeNodeHandlers('wcf\system\html\output\node\HtmlOutputNode', ['woltlab-metacode']);

        if ($this->outputType !== 'text/html') {
            // convert `<p>...</p>` into `...<br><br>`
            foreach ($this->getXPath()->query('//p') as $paragraph) {
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
                    // Add an extra `<br>` unless the paragraph already contains a `<br>`.
                    if ($paragraph->childNodes->length !== 1 || $paragraph->childNodes->item(0)->nodeName !== 'br') {
                        $br = $this->getDocument()->createElement('br');
                        $paragraph->appendChild($br);
                    }

                    $br = $this->getDocument()->createElement('br');
                    $paragraph->appendChild($br);
                }

                DOMUtil::removeNode($paragraph, true);
            }
            // Add a whitespace before and after each `<wcfNode-*>`.
            // This is necessary to avoid concatenation with neighbouring text.
            foreach ($this->getXPath()->query('//*') as $childNode) {
                if (!\str_starts_with($childNode->nodeName, 'wcfNode-')) {
                    continue;
                }

                $childNode->parentNode->insertBefore(
                    $this->getDocument()->createTextNode(" "),
                    $childNode
                );
                $childNode->parentNode->insertBefore(
                    $this->getDocument()->createTextNode(" "),
                    $childNode->nextSibling
                );
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
                    foreach ($this->getXPath()->query("//{$tagName}") as $element) {
                        $newline = $this->getDocument()->createTextNode("\n");
                        $element->parentNode->insertBefore($newline, $element->nextSibling);
                        DOMUtil::removeNode($element, true);
                    }
                }

                // remove all other elements
                foreach ($this->getXPath()->query('//*') as $element) {
                    DOMUtil::removeNode($element, true);
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

        parent::invokeHtmlNode($htmlNode);
    }

    protected function removeTextFormatting(): void
    {
        if (!\FORMATTING_REMOVE_COLOR && !\FORMATTING_REMOVE_FONT && !\FORMATTING_REMOVE_SIZE) {
            return;
        }

        /** @var list<\DOMElement> */
        $elementsWithStyles = [];
        foreach ($this->getXPath()->query("//*[@style]") as $element) {
            \assert($element instanceof \DOMElement);
            $elementsWithStyles[] = $element;
        }

        if ($elementsWithStyles === []) {
            return;
        }

        foreach ($elementsWithStyles as $element) {
            $style = $element->getAttribute("style");
            $values = \array_map(
                static fn (string $value) => StringUtil::trim($value),
                \explode(";", $style)
            );

            $values = \array_filter($values, static function (string $value) {
                [$keyword] = \explode(":", $value, 2);

                switch (StringUtil::trim($keyword)) {
                    case "color":
                        return \FORMATTING_REMOVE_COLOR === 0;

                    case "font-family":
                        return \FORMATTING_REMOVE_FONT === 0;

                    case "font-size":
                        return \FORMATTING_REMOVE_SIZE === 0;
                }

                return true;
            });

            $style = \implode(";", $values);
            if ($style !== "") {
                $element->setAttribute("style", $style);
                continue;
            }

            if ($element->tagName === "span") {
                $parent = $element->parentNode;
                while ($element->hasChildNodes()) {
                    $parent->insertBefore($element->childNodes->item(0), $element);
                }
                $element->remove();
            } else {
                $element->removeAttribute("style");
            }
        }
    }
}
