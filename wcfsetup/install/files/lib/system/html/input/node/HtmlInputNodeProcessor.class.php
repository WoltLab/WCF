<?php

namespace wcf\system\html\input\node;

use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\HtmlNodePlainLink;
use wcf\system\html\node\HtmlNodeUnfurlLink;
use wcf\system\html\node\IHtmlNode;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;
use wcf\system\worker\AbstractWorker;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes HTML nodes and handles bbcodes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputNodeProcessor extends AbstractHtmlNodeProcessor
{
    /**
     * list of allowed CSS class names per tag name
     * @var array<array>
     */
    public static $allowedClassNames = [
        'figure' => ['image', 'image-style-side', 'image-style-side-left'],
        'h2' => ['text-center', 'text-justify', 'text-right'],
        'h3' => ['text-center', 'text-justify', 'text-right'],
        'h4' => ['text-center', 'text-justify', 'text-right'],
        'img' => [
            // float left/right
            'messageFloatObjectLeft',
            'messageFloatObjectRight',

            // built-in
            'smiley',
            'woltlabAttachment',
            'woltlabSuiteMedia',
        ],
        'li' => ['text-center', 'text-justify', 'text-right'],
        'mark' => ['marker-error', 'marker-info', 'marker-success', 'marker-warning'],
        'p' => ['text-center', 'text-justify', 'text-right'],
        'pre' => ['woltlabHtml'],
        'td' => ['text-center', 'text-justify', 'text-right'],
    ];

    /**
     * List of HTML elements that should allow for custom CSS using
     * the `style`-attribute.
     *
     * Unfortunately, HTMLPurifier offers no *sane* way to limit this
     * attribute to some elements only.
     *
     * @var string[]
     */
    public static $allowedStyleElements = [
        'span',
    ];

    /**
     * list of HTML elements that are treated as empty, that means
     * they don't generate any (indirect) output at all
     *
     * @var string[]
     */
    public static $emptyTags = [
        // typical wrappers
        'div',
        'p',
        'span',

        // headlines
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',

        // tables
        'table',
        'tbody',
        'thead',
        'tr',
        'th',
        'td',
        'colgroup',
        'col',

        // lists
        'ul',
        'ol',
        'li',

        // other
        'a',
        'kbd',
        'woltlab-quote',
        'woltlab-spoiler',
        'pre',
        'sub',
        'sup',
        'strong',
        'del',
        'em',
        'u',
    ];

    /**
     * list of tag names that represent inline content in the HTML 5 standard
     * @var string[]
     */
    public static $inlineElements = [
        'a',
        'abbr',
        'acronym',
        'audio',
        'b',
        'bdi',
        'bdo',
        'big',
        'br',
        'button',
        'canvas',
        'cite',
        'code',
        'data',
        'datalist',
        'del',
        'dfn',
        'em',
        'embed',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'map',
        'mark',
        'meter',
        'noscript',
        'object',
        'output',
        'picture',
        'progress',
        'q',
        'ruby',
        's',
        'samp',
        'script',
        'select',
        'slot',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'svg',
        'template',
        'textarea',
        'time',
        'u',
        'tt',
        'var',
        'video',
        'wbr',
    ];

    /**
     * @var HtmlNodePlainLink[]
     */
    public $plainLinks = [];

    /**
     * list of embedded content grouped by type
     * @var array
     */
    protected $embeddedContent = [];

    /**
     * @inheritDoc
     */
    protected $nodeInterface = IHtmlInputNode::class;

    /**
     * @inheritDoc
     */
    public function process()
    {
        $this->plainLinks = [];

        EventHandler::getInstance()->fireAction($this, 'beforeProcess');

        // fix invalid html such as metacode markers outside of block elements
        $this->fixDom();

        // process metacode markers first
        $this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());

        // handle static converters
        $this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());

        if (MESSAGE_MAX_QUOTE_DEPTH) {
            $this->enforceQuoteDepth(MESSAGE_MAX_QUOTE_DEPTH);
        }

        $imgNodeHandler = new HtmlInputNodeImg();
        $this->invokeHtmlNode($imgNodeHandler);
        $smileyCount = $imgNodeHandler->getSmileyCount();

        // dynamic node handlers
        $this->invokeNodeHandlers('wcf\system\html\input\node\HtmlInputNode', ['img', 'woltlab-metacode']);

        // remove whitespace at the start/end of the message
        $this->trim();

        // detect mentions, urls, emails and smileys
        $textParser = new HtmlInputNodeTextParser($this, $smileyCount);
        $textParser->parse();

        // handle HTML bbcode
        $allowHtml = BBCodeHandler::getInstance()->isAvailableBBCode('html');

        // strip invalid class names
        /** @var \DOMElement $element */
        foreach ($this->getXPath()->query('//*[@class]') as $element) {
            $nodeName = $element->nodeName;
            if (isset(self::$allowedClassNames[$nodeName])) {
                if (self::$allowedClassNames[$nodeName] === '*') {
                    continue;
                }

                $classNames = \explode(' ', $element->getAttribute('class'));
                $classNames = \array_filter($classNames, static function ($className) use ($allowHtml, $nodeName) {
                    if (!$allowHtml && $nodeName === 'pre' && $className === 'woltlabHtml') {
                        return false;
                    }

                    return $className && \in_array($className, self::$allowedClassNames[$nodeName]);
                });

                if (!empty($classNames)) {
                    $element->setAttribute('class', \implode(' ', $classNames));
                    continue;
                }
            }

            $element->removeAttribute('class');

            if ($nodeName === 'span' && $element->attributes->length === 0) {
                DOMUtil::removeNode($element, true);
            }
        }

        EventHandler::getInstance()->fireAction($this, 'beforeEmbeddedProcess');

        $this->convertPlainLinks();

        // extract embedded content
        $this->processEmbeddedContent();

        EventHandler::getInstance()->fireAction($this, 'afterProcess');
    }

    /**
     * Enforces the maximum depth of nested quotes.
     *
     * @param int $depth
     */
    public function enforceQuoteDepth($depth, bool $isFullQuote = false)
    {
        $quotes = [];
        /** @var \DOMElement $quote */
        foreach ($this->getDocument()->getElementsByTagName('woltlab-quote') as $quote) {
            $quotes[] = $quote;
        }

        $checkQuotes = [];
        foreach ($quotes as $quote) {
            if (!$quote->parentNode) {
                continue;
            }

            if ($depth === 0) {
                DOMUtil::removeNode($quote);
            } else {
                $level = 0;
                $parent = $quote;
                while ($parent = $parent->parentNode) {
                    if ($parent->nodeName === 'woltlab-quote') {
                        $level++;
                    }
                }

                if ($level < $depth) {
                    continue;
                }

                $checkQuotes[] = $quote->parentNode;
                DOMUtil::removeNode($quote);
            }
        }

        /**
         * @var \DOMElement $quote
         */
        foreach ($checkQuotes as $quote) {
            if ($quote->childNodes->length === 0) {
                $quote->textContent = "[\u{2026}]";
            }
        }

        // Check if the quoted message is now empty.
        if ($depth === 0 && $isFullQuote && \count($quotes) > 0) {
            /** @var \DOMElement $body */
            $body = $this->getDocument()->getElementsByTagName('body')[0];
            if ($body->childElementCount === 0) {
                $p = $body->ownerDocument->createElement('p');
                $p->textContent = "[\u{2026}]";
                $body->appendChild($p);
            }
        }
    }

    /**
     * Fixes malformed HTML with metacode markers and text being placed
     * outside of paragraphs.
     */
    protected function fixDom()
    {
        // remove or convert any <div> found
        $elements = $this->getDocument()->getElementsByTagName('div');
        while ($elements->length) {
            $element = $elements->item(0);

            if ($element->parentNode->nodeName === 'P') {
                DOMUtil::removeNode($element, true);
            } else {
                DOMUtil::replaceElement($element, $element->ownerDocument->createElement('p'), true);
            }
        }

        $appendToPreviousParagraph = static function ($node) {
            /** @var \DOMElement $paragraph */
            $paragraph = $node->previousSibling;

            if (!$paragraph || $paragraph->nodeName !== 'p') {
                $paragraph = $node->ownerDocument->createElement('p');
                $node->parentNode->insertBefore($paragraph, $node);
            }

            $paragraph->appendChild($node);

            return $paragraph;
        };

        /** @var \DOMNode $node */
        $node = $this->getDocument()->getElementsByTagName('body')->item(0)->firstChild;
        while ($node) {
            if ($node->nodeType === \XML_ELEMENT_NODE && $node->nodeName === 'woltlab-metacode-marker') {
                $node = $appendToPreviousParagraph($node);
            } elseif ($node->nodeType === \XML_ELEMENT_NODE && \in_array($node->nodeName, self::$inlineElements)) {
                $node = $appendToPreviousParagraph($node);
            } elseif ($node->nodeType === \XML_TEXT_NODE) {
                // text node contains only a line break
                if ($node->textContent === "\n" || $node->textContent === "\r\n") {
                    // check if the previous node is a <p>, otherwise ignore this node entirely
                    if ($node->previousSibling === null || $node->previousSibling->nodeName !== 'p') {
                        $node = $node->nextSibling;
                        continue;
                    }
                }

                $node = $appendToPreviousParagraph($node);
            }

            $node = $node->nextSibling;
        }

        // Remove style attributes from non-whitelisted elements.
        /** @var \DOMElement $element */
        foreach ($this->getXPath()->query('//*[@style]') as $element) {
            if (!\in_array($element->nodeName, self::$allowedStyleElements)) {
                $element->removeAttribute('style');
            }
        }
    }

    /**
     * Trims leading and trailing whitespace. It will only remove text nodes containing
     * just whitespaces and <p><br></p> (including any whitespace-only text nodes).
     *
     * It is still possible to work around this by inserting useless text formats such
     * as bold to circumvent this check. The point of this method is to remove unintentional
     * and/or potentially unwanted whitespace, not guarding against people being jerks.
     */
    protected function trim()
    {
        $body = $this->getDocument()->getElementsByTagName('body')->item(0);

        foreach (['firstChild', 'lastChild'] as $property) {
            while ($node = $body->{$property}) {
                if ($node->nodeType === \XML_TEXT_NODE) {
                    if (StringUtil::trim($node->textContent) === '') {
                        $body->removeChild($node);
                    } else {
                        break;
                    }
                } else {
                    /** @var \DOMElement $node */
                    if ($node->nodeName === 'p') {
                        for ($i = 0, $length = $node->childNodes->length; $i < $length; $i++) {
                            $child = $node->childNodes->item($i);
                            if ($child->nodeType === \XML_TEXT_NODE) {
                                if (StringUtil::trim($child->textContent) !== '') {
                                    // terminate for() and while()
                                    break 2;
                                }
                            } elseif ($child->nodeName !== 'br') {
                                // terminate for() and while()
                                break 2;
                            }
                        }

                        $body->removeChild($node);
                    } else {
                        break;
                    }
                }
            }
        }

        // strip empty <p></p> (zero content, not even whitespaces)
        $paragraphs = DOMUtil::getElements($this->getDocument(), 'p');
        foreach ($paragraphs as $paragraph) {
            if ($paragraph->childNodes->length === 0) {
                DOMUtil::removeNode($paragraph);
            }
        }

        // trim <p>...</p>
        /** @var \DOMElement $paragraph */
        foreach ($this->getDocument()->getElementsByTagName('p') as $paragraph) {
            DOMUtil::normalize($paragraph);

            // CKEditor 5 exports empty paragraphs as `<p>&nbsp;</p>`.
            if ($paragraph->childNodes->length === 1) {
                $node = $paragraph->childNodes->item(0);
                if ($node->nodeType === \XML_TEXT_NODE && $node->textContent === "\u{00a0}") {
                    $br = $node->ownerDocument->createElement("br");
                    $br->setAttribute("data-cke-filler", "true");
                    $node->parentNode->appendChild($br);
                    $node->parentNode->removeChild($node);

                    continue;
                }
            }

            if ($paragraph->firstChild && $paragraph->firstChild->nodeType === \XML_TEXT_NODE) {
                $oldNode = $paragraph->firstChild;
                $newNode = $paragraph->ownerDocument->createTextNode(
                    \preg_replace('/^[\p{Zs}\s]+/u', '', $oldNode->textContent)
                );
                $paragraph->insertBefore($newNode, $oldNode);
                $paragraph->removeChild($oldNode);
            }

            if ($paragraph->lastChild && $paragraph->lastChild->nodeType === \XML_TEXT_NODE) {
                $oldNode = $paragraph->lastChild;
                $newNode = $paragraph->ownerDocument->createTextNode(
                    \preg_replace('/[\p{Zs}\s]+$/u', '', $oldNode->textContent)
                );
                $paragraph->insertBefore($newNode, $oldNode);
                $paragraph->removeChild($oldNode);
            }
        }

        // trim quotes
        /** @var \DOMElement $quote */
        foreach ($this->getDocument()->getElementsByTagName('woltlab-quote') as $quote) {
            $removeElements = [];
            for ($i = 0, $length = $quote->childNodes->length; $i < $length; $i++) {
                $node = $quote->childNodes->item($i);
                if ($node->nodeType === \XML_TEXT_NODE) {
                    continue;
                }

                if ($node->nodeName === 'p' && $node->childNodes->length === 1) {
                    $child = $node->childNodes->item(0);
                    if ($child->nodeType === \XML_ELEMENT_NODE && $child->nodeName === 'br') {
                        $removeElements[] = $node;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }

            foreach ($removeElements as $removeElement) {
                $quote->removeChild($removeElement);
            }

            $removeElements = [];
            for ($i = $quote->childNodes->length - 1; $i >= 0; $i--) {
                $node = $quote->childNodes->item($i);
                if ($node->nodeType === \XML_TEXT_NODE) {
                    continue;
                }

                if ($node->nodeName === 'p' && $node->childNodes->length === 1) {
                    $child = $node->childNodes->item(0);
                    if ($child->nodeType === \XML_ELEMENT_NODE && $child->nodeName === 'br') {
                        $removeElements[] = $node;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }

            foreach ($removeElements as $removeElement) {
                $quote->removeChild($removeElement);
            }
        }

        // Strip de facto empty text nodes that are the result from quirky formatting, for example:
        // <p><span style="font-size: 12px"> </span></p>
        /** @var \DOMElement $paragraph */
        foreach ($this->getXPath()->query('//p') as $paragraph) {
            $textContent = StringUtil::trim($paragraph->textContent);
            if ($textContent !== '') {
                continue;
            }

            /** @var \DOMElement $element */
            foreach ($paragraph->getElementsByTagName('*') as $element) {
                if (!\in_array($element->nodeName, self::$emptyTags)) {
                    continue 2;
                }
            }

            // Do not strip content that contains non empty spans, such as icons.
            /** @var \DOMElement $element */
            foreach ($paragraph->getElementsByTagName('span') as $element) {
                if ($element->getAttribute('class')) {
                    continue 2;
                }
            }

            $paragraph->parentNode->removeChild($paragraph);
        }
    }

    /**
     * Checks the input html for disallowed bbcodes and returns any matches.
     *
     * @return      string[]        list of matched disallowed bbcodes
     */
    public function validate()
    {
        $result = [];

        $this->invokeNodeHandlers(
            'wcf\system\html\input\node\HtmlInputNode',
            [],
            function (IHtmlNode $nodeHandler) use (&$result) {
                $disallowed = $nodeHandler->isAllowed($this);
                if ($disallowed) {
                    $result = \array_merge($result, $disallowed);
                }
            }
        );

        // handle custom nodes that have no dedicated handler
        $customTags = [
            'spoiler' => 'woltlab-spoiler',
            'url' => 'a',
        ];

        foreach ($customTags as $bbcode => $tagName) {
            if (BBCodeHandler::getInstance()->isAvailableBBCode($bbcode)) {
                continue;
            }

            if ($this->getDocument()->getElementsByTagName($tagName)->length) {
                $result[] = $bbcode;
            }
        }

        $inlineStyles = [
            'color' => 'color',
            'font' => 'font-family',
            'size' => 'font-size',
        ];
        foreach ($inlineStyles as $bbcode => $property) {
            if (BBCodeHandler::getInstance()->isAvailableBBCode($bbcode)) {
                unset($inlineStyles[$bbcode]);
            }
        }

        if (!empty($inlineStyles)) {
            /** @var \DOMElement $element */
            foreach ($this->getXPath()->query('//*[@style]') as $element) {
                $tmp = \array_filter(\explode(';', $element->getAttribute('style')));
                foreach ($tmp as $style) {
                    $property = \explode(':', $style, 2)[0];
                    if (\in_array($property, $inlineStyles) && !\in_array($property, $result)) {
                        $result[] = $property;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns the raw text content of current document.
     *
     * @return      string          raw text content
     */
    public function getTextContent()
    {
        // cloning the body allows custom event handlers to alter the contents
        // without making permanent changes to the document, avoids side-effects
        $document = clone $this->getDocument();

        // insert a trailing whitespace plus newline for certain elements, such as `<br>` or `<li>`
        $tagNames = HtmlOutputNodeProcessor::$plainTextNewlineTags;
        $tagNames[] = 'p';

        $xpath = new \DOMXPath($document);
        foreach ($tagNames as $tagName) {
            foreach ($xpath->query("//{$tagName}") as $element) {
                $newline = $document->createTextNode(" \n");
                $element->parentNode->insertBefore($newline, $element->nextSibling);
                DOMUtil::removeNode($element, true);
            }
        }

        $parameters = ['body' => $document->getElementsByTagName('body')->item(0)];
        EventHandler::getInstance()->fireAction($this, 'getTextContent', $parameters);

        return StringUtil::trim($parameters['body']->textContent);
    }

    /**
     * Returns true if the message appears to be empty.
     *
     * @return      bool         true if message appears to be empty
     */
    public function appearsToBeEmpty()
    {
        if ($this->getTextContent() !== '') {
            return false;
        }

        /** @var \DOMElement $body */
        $body = $this->getDocument()->getElementsByTagName('body')->item(0);

        /** @var \DOMElement $element */
        foreach ($body->getElementsByTagName('*') as $element) {
            if (!\in_array($element->nodeName, self::$emptyTags)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Processes embedded content.
     */
    public function processEmbeddedContent()
    {
        $this->embeddedContent = [];

        $this->parseEmbeddedContent();
    }

    /**
     * Returns the embedded content grouped by type.
     *
     * @return      array
     */
    public function getEmbeddedContent()
    {
        return $this->embeddedContent;
    }

    /**
     * Add embedded content for provided type.
     *
     * @param string $type type name
     * @param array $data embedded content
     */
    public function addEmbeddedContent($type, array $data)
    {
        if (isset($this->embeddedContent[$type])) {
            $this->embeddedContent[$type] = \array_merge($this->embeddedContent[$type], $data);
        } else {
            $this->embeddedContent[$type] = $data;
        }
    }

    /**
     * Parses embedded content contained in metacode elements.
     */
    protected function parseEmbeddedContent()
    {
        // handle `woltlab-metacode`
        $elements = $this->getDocument()->getElementsByTagName('woltlab-metacode');
        $metacodesByName = [];
        for ($i = 0, $length = $elements->length; $i < $length; $i++) {
            /** @var \DOMElement $element */
            $element = $elements->item($i);
            $name = $element->getAttribute('data-name');
            $attributes = $this->parseAttributes($element->getAttribute('data-attributes'));

            if (!isset($metacodesByName[$name])) {
                $metacodesByName[$name] = [];
            }
            $metacodesByName[$name][] = $attributes;
        }

        $this->embeddedContent = $metacodesByName;

        EventHandler::getInstance()->fireAction($this, 'parseEmbeddedContent');
    }

    /**
     * Creates a new `<woltlab-metacode>` element contained in the same document
     * as the provided `$node`.
     *
     * @param \DOMNode $node reference node used to extract the owner document
     * @param string $name metacode name
     * @param mixed[] $attributes list of attributes
     * @return      \DOMElement     new metacode element
     */
    public function createMetacodeElement(\DOMNode $node, $name, array $attributes)
    {
        $element = $node->ownerDocument->createElement('woltlab-metacode');
        $element->setAttribute('data-name', $name);
        $element->setAttribute('data-attributes', \base64_encode(\json_encode($attributes)));

        return $element;
    }

    /**
     * Detects links that contain nothing but their link target. Additionally, standalone links, i. e.
     * those that are the only content in their line, are offered separately.
     *
     * @since 5.2
     */
    protected function convertPlainLinks()
    {
        /** @var \DOMElement $link */
        foreach ($this->getDocument()->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if ($href !== $link->textContent) {
                continue;
            }

            $plainLink = new HtmlNodePlainLink($link, $href);

            // Check if the line appears to only contain the link text.
            $parent = $link;
            while ($parent->parentNode->nodeName !== 'body') {
                /** @var \DOMElement $parent */
                $parent = $parent->parentNode;
            }
            $mayContainOtherContent = false;
            $linebreaks = 0;

            if ($parent->nodeName === 'p' && $parent->textContent === $link->textContent) {
                // The line may contain nothing but the link, exceptions include basic formatting
                // and up to a single `<br>` element.
                /** @var \DOMElement $element */
                foreach ($parent->getElementsByTagName('*') as $element) {
                    if ($this->mayContainOtherContent($element, $linebreaks)) {
                        $mayContainOtherContent = true;
                        break;
                    }
                }

                if (!$mayContainOtherContent || $linebreaks <= 1) {
                    $this->plainLinks[] = $plainLink->setIsStandalone($parent);
                    continue;
                }
            } elseif ($parent->nodeName === 'p') {
                $parentLinkElement = $link;
                while ($parentLinkElement->parentElement !== $parent) {
                    $parentLinkElement = $parentLinkElement->parentElement;
                    if ($this->mayContainOtherContent($parentLinkElement, $linebreaks)) {
                        $mayContainOtherContent = true;
                        break;
                    }
                }

                if (!$mayContainOtherContent) {
                    $nextSibling = $this->getNoneEmptyNode($parentLinkElement, 'nextSibling');
                    $previousSibling = $this->getNoneEmptyNode($parentLinkElement, 'previousSibling');

                    // Check whether the link is at the beginning or end of the paragraph
                    // and whether the next or previous sibling is a line break.
                    // <p><a href="https://example.com">https://example.com</a><br>…</p>
                    // <p>…<br><a href="https://example.com">https://example.com</a></p>
                    if (
                        ($nextSibling === null && $previousSibling !== null && $previousSibling->nodeName === 'br')
                        || ($previousSibling === null && $nextSibling !== null && $nextSibling->nodeName === 'br')
                    ) {
                        $this->plainLinks[] = $plainLink->setIsStandalone($parent, false);
                        continue;
                    }
                    // If not, the previous and next sibling may be a line break.
                    // <p>…<br><a href="https://example.com">https://example.com</a><br>…</p>
                    // <p>…<br><u><b><a href="https://example.com">https://example.com</a></b></u><br>…</p>
                    if (
                        $previousSibling === null && $nextSibling === null
                        || (
                            $previousSibling !== null && $nextSibling !== null
                            && $previousSibling->nodeName === 'br' && $nextSibling->nodeName === 'br'
                        )
                    ) {
                        $this->plainLinks[] = $plainLink->setIsStandalone($parent, false);
                        continue;
                    }
                }
            }

            $this->plainLinks[] = $plainLink->setIsInline();
        }

        EventHandler::getInstance()->fireAction($this, 'convertPlainLinks');

        $isWorkerAction = \class_exists(AbstractWorker::class, false);
        if (MODULE_URL_UNFURLING && !$isWorkerAction) {
            foreach ($this->plainLinks as $plainLink) {
                if ($plainLink->isPristine()) {
                    HtmlNodeUnfurlLink::setUnfurl($plainLink);
                }
            }
        }
    }

    private function getNoneEmptyNode(?\DOMNode $element, string $property): ?\DOMNode
    {
        while ($element = $element->{$property}) {
            if (!DOMUtil::isEmpty($element)) {
                return $element;
            }
        }

        return null;
    }

    private function mayContainOtherContent(\DOMElement $element, int &$linebreaks): bool
    {
        switch ($element->nodeName) {
            case 'br':
                $linebreaks++;
                break;

            case 'span':
                if ($element->getAttribute('class')) {
                    return true;
                }

                // `<span>` is used to hold text formatting.
                break;

            case 'a':
            case 'b':
            case 'em':
            case 'i':
            case 'strong':
            case 'u':
                // These elements are perfectly fine.
                break;

            default:
                return true;
        }

        return false;
    }
}
