<?php
namespace wcf\system\html\node;
use wcf\system\exception\SystemException;
use wcf\system\html\IHtmlProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * Default implementation for html node processors.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Node
 * @since       3.0
 */
abstract class AbstractHtmlNodeProcessor implements IHtmlNodeProcessor {
	/**
	 * active DOM document
	 * @var	\DOMDocument
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
	 * @inheritDOc
	 */
	public function load(IHtmlProcessor $htmlProcessor, $html) {
		$this->htmlProcessor = $htmlProcessor;
		
		$this->document = new \DOMDocument('1.0', 'UTF-8');
		$this->xpath = null;
		
		// Ignore all errors when loading the HTML string, because DOMDocument does not
		// provide a proper way to add custom HTML elements (even though explicitly allowed
		// in HTML5) and the input HTML has already been sanitized by HTMLPurifier.
		// 
		// We're also injecting a bogus meta tag that magically enables DOMDocument
		// to handle UTF-8 properly. This avoids encoding non-ASCII characters as it
		// would conflict with already existing entities when reverting them.
		@$this->document->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $html);
		
		$this->nodeData = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		$html = $this->document->saveHTML($this->document->getElementsByTagName('body')->item(0));
		
		// remove nuisance added by PHP
		$html = preg_replace('~^<body>~', '', $html);
		$html = preg_replace('~</body>$~', '', $html);
		
		/** @var IHtmlNode $obj */
		foreach ($this->nodeData as $data) {
			$obj = $data['object'];
			$string = $obj->replaceTag($data['data']);
			$html = preg_replace_callback('~<wcfNode-' . $data['identifier'] . '>(?P<content>[\s\S]*)</wcfNode-' . $data['identifier'] . '>~', function($matches) use ($string) {
				$string = str_replace('<!-- META_CODE_INNER_CONTENT -->', $matches['content'], $string);
				
				return $string;
			}, $html);
			
		}
		
		return $html;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDocument() {
		return $this->document;
	}
	
	/**
	 * Returns a XPath instance for the current DOM document.
	 * 
	 * @return      \DOMXPath       XPath instance
	 */
	public function getXPath() {
		if ($this->xpath === null) {
			$this->xpath = new \DOMXPath($this->getDocument());
		}
		
		return $this->xpath;
	}
	
	/**
	 * Renames a tag by creating a new element, moving all child nodes and
	 * eventually removing the original element.
	 * 
	 * @param       \DOMElement     $element        old element
	 * @param       string          $tagName        tag name for the new element
	 * @return      \DOMElement     newly created element
	 */
	public function renameTag(\DOMElement $element, $tagName) {
		$newElement = $this->document->createElement($tagName);
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
	 * @param       \DOMElement     $element        target element
	 * @param       string          $text           text used to replace target 
	 * @param       boolean         $isBlockElement true if element is a block element
	 */
	public function replaceElementWithText(\DOMElement $element, $text, $isBlockElement) {
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
	 * @param       \DOMElement     $element        element to be removed
	 */
	public function unwrapContent(\DOMElement $element) {
		while ($element->hasChildNodes()) {
			$element->parentNode->insertBefore($element->firstChild, $element);
		}
		
		$element->parentNode->removeChild($element);
	}
	
	/**
	 * Adds node replacement data.
	 * 
	 * @param       IHtmlNode       $htmlNode               node processor instance
	 * @param       string          $nodeIdentifier         replacement node identifier
	 * @param       array           $data                   replacement data
	 */
	public function addNodeData(IHtmlNode $htmlNode, $nodeIdentifier, array $data) {
		$this->nodeData[] = [
			'data' => $data,
			'identifier' => $nodeIdentifier,
			'object' => $htmlNode
		];
	}
	
	/**
	 * Parses an attribute string.
	 * 
	 * @param       string          $attributes             base64 and JSON encoded attributes
	 * @return      array           parsed attributes
	 */
	public function parseAttributes($attributes) {
		if (empty($attributes)) {
			return [];
		}
		
		$parsedAttributes = base64_decode($attributes, true);
		if ($parsedAttributes !== false) {
			try {
				$parsedAttributes = JSON::decode($parsedAttributes);
			}
			catch (SystemException $e) {
				/* parse errors can occur if user provided malicious content - ignore them */
				$parsedAttributes = [];
			}
		}
		
		return $parsedAttributes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlProcessor() {
		return $this->htmlProcessor;
	}
	
	/**
	 * Invokes a html node processor.
	 * 
	 * @param       IHtmlNode       $htmlNode       html node processor
	 */
	protected function invokeHtmlNode(IHtmlNode $htmlNode) {
		if (!($htmlNode instanceof $this->nodeInterface)) {
			throw new \InvalidArgumentException("Node '" . get_class($htmlNode) . "' does not implement the interface '" . $this->nodeInterface . "'.");
		}
		
		$tagName = $htmlNode->getTagName();
		if (empty($tagName)) {
			throw new \UnexpectedValueException("Missing tag name for " . get_class($htmlNode));
		}
		
		$elements = [];
		foreach ($this->getDocument()->getElementsByTagName($tagName) as $element) {
			$elements[] = $element;
		}
		
		if (!empty($elements)) {
			$htmlNode->process($elements, $this);
		}
	}
	
	/**
	 * Invokes possible html node processors based on found element tag names.
	 * 
	 * @param       string          $classNamePattern       full namespace pattern for class guessing
	 * @param       string[]        $skipTags               list of tag names that should be ignored
	 */
	protected function invokeNodeHandlers($classNamePattern, array $skipTags = []) {
		$skipTags = array_merge($skipTags, ['html', 'head', 'title', 'meta', 'body', 'link']);
		
		$tags = [];
		/** @var \DOMElement $tag */
		foreach ($this->getDocument()->getElementsByTagName('*') as $tag) {
			$tagName = $tag->nodeName;
			if (!isset($tags[$tagName])) $tags[$tagName] = $tagName;
		}
		
		foreach ($tags as $tagName) {
			if (in_array($tagName, $skipTags)) {
				continue;
			}
			
			$tagName = preg_replace_callback('/-([a-z])/', function($matches) {
				return ucfirst($matches[1]);
			}, $tagName);
			$className = $classNamePattern . ucfirst($tagName);
			if (class_exists($className)) {
				$this->invokeHtmlNode(new $className);
			}
		}
	}
}
