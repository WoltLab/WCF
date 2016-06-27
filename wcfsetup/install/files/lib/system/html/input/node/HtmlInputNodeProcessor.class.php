<?php
namespace wcf\system\html\input\node;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

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
		
		$this->embeddedContent = [];
		
		// process metacode markers first
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());
		
		// handle static converters
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());
		$this->invokeHtmlNode(new HtmlInputNodeImg());
		
		// dynamic node handlers
		$this->invokeNodeHandlers('wcf\system\html\input\node\HtmlInputNode', ['img', 'woltlab-metacode']);
		
		// detect mentions, urls, emails and smileys
		$textParser = new HtmlInputNodeTextParser($this);
		$textParser->parse();
		
		// extract embedded content
		$this->parseEmbeddedContent();
		
		EventHandler::getInstance()->fireAction($this, 'afterProcess');
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
