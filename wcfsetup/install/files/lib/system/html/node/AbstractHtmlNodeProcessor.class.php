<?php

namespace wcf\system\html\node;

use wcf\system\exception\SystemException;
use wcf\system\html\IHtmlProcessor;
use wcf\util\JSON;

/**
 * Default implementation for html node processors.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
abstract class AbstractHtmlNodeProcessor implements IHtmlNodeProcessor
{
    /**
     * active DOM document
     * @var \DOMDocument
     */
    protected $document;

    /**
     * html processor instance
     * @var IHtmlProcessor
     */
    protected $htmlProcessor;

    /**
     * required interface for html nodes
     * @var string
     */
    protected $nodeInterface = '';

    /**
     * storage for node replacements
     * @var array
     */
    protected $nodeData = [];

    /**
     * XPath instance
     * @var \DOMXPath
     */
    protected $xpath;

    /**
     * @inheritDoc
     */
    public function load(IHtmlProcessor $htmlProcessor, $html)
    {
        $this->htmlProcessor = $htmlProcessor;

        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->xpath = null;

        $html = \preg_replace_callback('~(<pre[^>]*>)(.*?)(</pre>)~s', static function ($matches) {
            return $matches[1] . \preg_replace('~\r?\n~', '@@@WCF_PRE_LINEBREAK@@@', $matches[2]) . $matches[3];
        }, $html);

        // strip UTF-8 zero-width whitespace
        $html = \preg_replace('~\x{200B}~u', '', $html);

        // discard any non-breaking spaces
        $html = \str_replace('&nbsp;', ' ', $html);

        // work-around for a libxml bug that causes a single space between
        // some inline elements to be dropped
        $html = \str_replace('> <', '>&nbsp;<', $html);

        // Ignore all errors when loading the HTML string, because DOMDocument does not
        // provide a proper way to add custom HTML elements (even though explicitly allowed
        // in HTML5) and the input HTML has already been sanitized by HTMLPurifier.
        //
        // We're also injecting a bogus meta tag that magically enables DOMDocument
        // to handle UTF-8 properly. This avoids encoding non-ASCII characters as it
        // would conflict with already existing entities when reverting them.
        $useInternalErrors = \libxml_use_internal_errors(true);
        $this->document->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $html . '</body></html>'
        );

        // flush libxml's error buffer, after all we don't care for any errors caused
        // by the `loadHTML()` call above anyway
        \libxml_clear_errors();
        \libxml_use_internal_errors($useInternalErrors);

        // fix the `<pre>` linebreaks again
        $pres = $this->document->getElementsByTagName('pre');
        for ($i = 0, $length = $pres->length; $i < $length; $i++) {
            /** @var \DOMNode $node */
            foreach ($this->getXPath()->query('./text()', $pres->item($i)) as $node) {
                if (\str_contains($node->textContent, '@@@WCF_PRE_LINEBREAK@@@')) {
                    $node->nodeValue = \str_replace('@@@WCF_PRE_LINEBREAK@@@', "\n", $node->textContent);
                }
            }
        }

        $this->nodeData = [];
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        $html = $this->document->saveHTML($this->document->getElementsByTagName('body')->item(0));

        // remove nuisance added by PHP
        $html = \preg_replace('~^<!DOCTYPE[^>]+>\n~', '', $html);
        $html = \preg_replace('~^<body>~', '', $html);
        $html = \preg_replace('~</body>$~', '', $html);

        foreach ($this->nodeData as $data) {
            $html = \preg_replace_callback(
                '~<wcfNode-' . $data['identifier'] . '>(?P<content>[\s\S]*)</wcfNode-' . $data['identifier'] . '>~',
                static function ($matches) use ($data) {
                    /** @var IHtmlNode $obj */
                    $obj = $data['object'];
                    $string = $obj->replaceTag($data['data']);

                    if (!isset($data['data']['skipInnerContent']) || $data['data']['skipInnerContent'] !== true) {
                        if (\str_contains($string, '<!-- META_CODE_INNER_CONTENT -->')) {
                            return \str_replace('<!-- META_CODE_INNER_CONTENT -->', $matches['content'], $string);
                        } elseif (\str_contains($string, '&lt;!-- META_CODE_INNER_CONTENT --&gt;')) {
                            return \str_replace(
                                '&lt;!-- META_CODE_INNER_CONTENT --&gt;',
                                $matches['content'],
                                $string
                            );
                        }
                    }

                    return $string;
                },
                $html
            );
        }

        // work-around for a libxml bug that causes a single space between
        // some inline elements to be dropped
        $html = \str_replace('&nbsp;', ' ', $html);

        return \preg_replace('~>\x{00A0}<~u', '> <', $html);
    }

    /**
     * @inheritDoc
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns a XPath instance for the current DOM document.
     *
     * @return      \DOMXPath       XPath instance
     */
    public function getXPath()
    {
        if ($this->xpath === null) {
            $this->xpath = new \DOMXPath($this->getDocument());
        }

        return $this->xpath;
    }

    /**
     * Renames a tag by creating a new element, moving all child nodes and
     * eventually removing the original element.
     *
     * @param \DOMElement $element old element
     * @param string $tagName tag name for the new element
     * @param bool $preserveAttributes retain attributes for the new element
     * @return      \DOMElement     newly created element
     */
    public function renameTag(\DOMElement $element, $tagName, $preserveAttributes = false)
    {
        $newElement = $this->document->createElement($tagName);
        if ($preserveAttributes) {
            /** @var \DOMNode $attribute */
            foreach ($element->attributes as $attribute) {
                $newElement->setAttribute($attribute->nodeName, $attribute->nodeValue);
            }
        }

        $element->parentNode->insertBefore($newElement, $element);
        while ($element->hasChildNodes()) {
            $newElement->appendChild($element->firstChild);
        }

        $element->parentNode->removeChild($element);

        return $newElement;
    }

    /**
     * Replaces an element with plain text.
     *
     * @param \DOMElement $element target element
     * @param string $text text used to replace target
     * @param bool $isBlockElement true if element is a block element
     */
    public function replaceElementWithText(\DOMElement $element, $text, $isBlockElement)
    {
        $textNode = $element->ownerDocument->createTextNode($text);
        $element->parentNode->insertBefore($textNode, $element);

        if ($isBlockElement) {
            for ($i = 0; $i < 2; $i++) {
                $br = $element->ownerDocument->createElement('br');
                $element->parentNode->insertBefore($br, $element);
            }
        }

        $element->parentNode->removeChild($element);
    }

    /**
     * Removes an element but preserves child nodes by moving them into
     * its original position.
     *
     * @param \DOMElement $element element to be removed
     */
    public function unwrapContent(\DOMElement $element)
    {
        while ($element->hasChildNodes()) {
            $element->parentNode->insertBefore($element->firstChild, $element);
        }

        $element->parentNode->removeChild($element);
    }

    /**
     * Adds node replacement data.
     *
     * @param IHtmlNode $htmlNode node processor instance
     * @param string $nodeIdentifier replacement node identifier
     * @param array $data replacement data
     */
    public function addNodeData(IHtmlNode $htmlNode, $nodeIdentifier, array $data)
    {
        $this->nodeData[] = [
            'data' => $data,
            'identifier' => $nodeIdentifier,
            'object' => $htmlNode,
        ];
    }

    /**
     * Parses an attribute string.
     *
     * @param string $attributes base64 and JSON encoded attributes
     * @return      array           parsed attributes
     */
    public function parseAttributes($attributes)
    {
        if (!empty($attributes)) {
            $parsedAttributes = \base64_decode($attributes, true);
            if ($parsedAttributes !== false) {
                try {
                    $parsedAttributes = JSON::decode($parsedAttributes);
                } catch (SystemException $e) {
                    /* parse errors can occur if user provided malicious content - ignore them */
                    $parsedAttributes = [];
                }

                return $parsedAttributes;
            }
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getHtmlProcessor()
    {
        return $this->htmlProcessor;
    }

    /**
     * Invokes a html node processor.
     *
     * @param IHtmlNode $htmlNode html node processor
     */
    protected function invokeHtmlNode(IHtmlNode $htmlNode)
    {
        if (!($htmlNode instanceof $this->nodeInterface)) {
            throw new \InvalidArgumentException(
                "Node '" . \get_class($htmlNode) . "' does not implement the interface '" . $this->nodeInterface . "'."
            );
        }

        $tagName = $htmlNode->getTagName();
        if (empty($tagName)) {
            throw new \UnexpectedValueException("Missing tag name for " . \get_class($htmlNode));
        }

        $elements = [];
        foreach ($this->getXPath()->query("//{$tagName}") as $element) {
            $elements[] = $element;
        }

        if (!empty($elements)) {
            $htmlNode->process($elements, $this);
        }
    }

    /**
     * Invokes possible html node processors based on found element tag names.
     *
     * @param string $classNamePattern full namespace pattern for class guessing
     * @param string[] $skipTags list of tag names that should be ignored
     * @param callable $callback optional callback
     */
    protected function invokeNodeHandlers($classNamePattern, array $skipTags = [], ?callable $callback = null)
    {
        static $handlerClassExists = [];

        $skipTags = \array_merge($skipTags, ['html', 'head', 'title', 'meta', 'body', 'link']);

        $tags = [];
        /** @var \DOMElement $tag */
        foreach ($this->getXPath()->query("//*") as $tag) {
            $tagName = $tag->nodeName;
            if (!isset($tags[$tagName])) {
                $tags[$tagName] = $tagName;
            }
        }

        foreach ($tags as $tagName) {
            if (
                \in_array($tagName, $skipTags)
                || \str_starts_with($tagName, 'wcfNode-')
            ) {
                continue;
            }

            $tagName = \preg_replace_callback('/-([a-z])/', static function ($matches) {
                return \ucfirst($matches[1]);
            }, $tagName);
            $className = $classNamePattern . \ucfirst($tagName);

            // The `\class_exists()` call has to go through the autoloader which
            // can become quite expensive when dealing with a lot of tags and
            // messages within one request.
            if (!isset($handlerClassExists[$className])) {
                $handlerClassExists[$className] = \class_exists($className);
            }

            if ($handlerClassExists[$className]) {
                if ($callback === null) {
                    $this->invokeHtmlNode(new $className());
                } else {
                    $callback(new $className());
                }
            }
        }
    }

    /**
     * Returns a randomly generated tagName+identifier pair for <wcfNode-*> tags.
     *
     * @return array{0: string, 1: string}
     */
    public function getWcfNodeIdentifer(): array
    {
        static $engine = null;

        if ($engine === null) {
            if (\class_exists(\Random\Engine\Xoshiro256StarStar::class, false)) {
                $randomizer = new \Random\Randomizer(new \Random\Engine\Xoshiro256StarStar());
                $engine = static fn () => \bin2hex($randomizer->getBytes(16));
            } else {
                $engine = static fn () => \bin2hex(\random_bytes(16));
            }
        }

        $identifier = $engine();

        return [$identifier, "wcfNode-{$identifier}"];
    }
}
