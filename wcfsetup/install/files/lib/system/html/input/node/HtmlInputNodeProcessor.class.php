<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\IHtmlNode;
use wcf\util\StringUtil;

/**
 * Processes HTML nodes and handles bbcodes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeProcessor extends AbstractHtmlNodeProcessor {
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
	public function process() {
		EventHandler::getInstance()->fireAction($this, 'beforeProcess');
		
		// process metacode markers first
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());
		
		// handle static converters
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());
		$this->invokeHtmlNode(new HtmlInputNodeImg());
		
		// dynamic node handlers
		$this->invokeNodeHandlers('wcf\system\html\input\node\HtmlInputNode', ['img', 'woltlab-metacode']);
		
		// remove whitespace at the start/end of the message
		$this->trim();
		
		// detect mentions, urls, emails and smileys
		$textParser = new HtmlInputNodeTextParser($this);
		$textParser->parse();
		
		// extract embedded content
		$this->processEmbeddedContent();
		
		EventHandler::getInstance()->fireAction($this, 'afterProcess');
	}
	
	/**
	 * Trims leading and trailing whitespace. It will only remove text nodes containing
	 * just whitespaces and <p><br></p> (including any whitespace-only text nodes).
	 * 
	 * It is still possible to work around this by inserting useless text formats such
	 * as bold to circumvent this check. The point of this method is to remove unintentional
	 * and/or potentially unwanted whitespace, not guarding against people being jerks.
	 */
	protected function trim() {
		$body = $this->getDocument()->getElementsByTagName('body')->item(0);
		
		foreach (['firstChild', 'lastChild'] as $property) {
			while ($node = $body->$property) {
				if ($node->nodeType === XML_TEXT_NODE) {
					if (StringUtil::trim($node->textContent) === '') {
						$body->removeChild($node);
					}
					else {
						break;
					}
				}
				else {
					/** @var \DOMElement $node */
					if ($node->nodeName === 'p') {
						for ($i = 0, $length = $node->childNodes->length; $i < $length; $i++) {
							/** @var \DOMNode $child */
							$child = $node->childNodes[$i];
							if ($child->nodeType === XML_TEXT_NODE) {
								if (StringUtil::trim($child->textContent) !== '') {
									// terminate for() and while()
									break 2;
								}
							}
							else if ($child->nodeName !== 'br') {
								// terminate for() and while()
								break 2;
							}
						}
						
						$body->removeChild($node);
					}
					else {
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Checks the input html for disallowed bbcodes and returns any matches.
	 * 
	 * @return      string[]        list of matched disallowed bbcodes
	 */
	public function validate() {
		$result = [];
		
		$this->invokeNodeHandlers('wcf\system\html\input\node\HtmlInputNode', [], function(IHtmlNode $nodeHandler) use (&$result) {
			$disallowed = $nodeHandler->isAllowed($this);
			if ($disallowed) {
				$result = array_merge($result, $disallowed);
			}
		});
		
		// handle custom nodes that have no dedicated handler
		$customTags = [
			'color' => 'woltlab-color',
			'font' => 'woltlab-size',
			'size' => 'woltlab-size',
			'spoiler' => 'woltlab-spoiler',
			'url' => 'a'
		];
		
		foreach ($customTags as $bbcode => $tagName) {
			if (BBCodeHandler::getInstance()->isAvailableBBCode($bbcode)) {
				continue;
			}
			
			if ($this->getDocument()->getElementsByTagName($tagName)->length) {
				$result[] = $bbcode;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns the raw text content of current document.
	 * 
	 * @return      string          raw text content
	 */
	public function getTextContent() {
		return StringUtil::trim($this->getDocument()->getElementsByTagName('body')->item(0)->textContent);
	}
	
	/**
	 * Processes embedded content.
	 */
	public function processEmbeddedContent() {
		$this->embeddedContent = [];
		
		$this->parseEmbeddedContent();
	}
	
	/**
	 * Returns the embedded content grouped by type.
	 * 
	 * @return      array
	 */
	public function getEmbeddedContent() {
		return $this->embeddedContent;
	}
	
	/**
	 * Add embedded content for provided type.
	 * 
	 * @param       string  $type   type name
	 * @param       array   $data   embedded content
	 */
	public function addEmbeddedContent($type, array $data) {
		if (isset($this->embeddedContent[$type])) {
			$this->embeddedContent[$type] = array_merge($this->embeddedContent[$type], $data);
		}
		else {
			$this->embeddedContent[$type] = $data;
		}
	}
	
	/**
	 * Parses embedded content containedin metacode elements.
	 */
	protected function parseEmbeddedContent() {
		// handle `woltlab-metacode`
		$elements = $this->getDocument()->getElementsByTagName('woltlab-metacode');
		$metacodesByName = [];
		for ($i = 0, $length = $elements->length; $i < $length; $i++) {
			/** @var \DOMElement $element */
			$element = $elements->item($i);
			$name = $element->getAttribute('data-name');
			$attributes = $this->parseAttributes($element->getAttribute('data-attributes'));
			
			if (!isset($metacodesByName[$name])) $metacodesByName[$name] = [];
			$metacodesByName[$name][] = $attributes;
		}
		
		$this->embeddedContent = $metacodesByName;
		
		EventHandler::getInstance()->fireAction($this, 'parseEmbeddedContent');
	}
	
	/**
	 * Creates a new `<woltlab-metacode>` element contained in the same document
	 * as the provided `$node`.
	 * 
	 * @param       \DOMNode        $node           reference node used to extract the owner document
	 * @param       string          $name           metacode name
	 * @param       mixed[]         $attributes     list of attributes
	 * @return      \DOMElement     new metacode element
	 */
	public function createMetacodeElement(\DOMNode $node, $name, array $attributes) {
		$element = $node->ownerDocument->createElement('woltlab-metacode');
		$element->setAttribute('data-name', $name);
		$element->setAttribute('data-attributes', base64_encode(json_encode($attributes)));
		
		return $element;
	}
}
