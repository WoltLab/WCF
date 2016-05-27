<?php
namespace wcf\system\html\input\node;
use wcf\system\event\EventHandler;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeProcessor extends HtmlNodeProcessor implements IHtmlInputNodeProcessor {
	protected $embeddedContent = [];
	
	// TODO: this should include other tags
	protected $emptyTags = ['em', 'strong', 'u'];
	
	// TODO: this should include other tags
	protected $mergeTags = ['em', 'strong', 'u'];
	
	public function process() {
		EventHandler::getInstance()->fireAction($this, 'beforeProcess');
		
		$this->embeddedContent = [];
		
		// process metacode markers first
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());
		
		// handle static converters
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());
		
		// extract embedded content
		$this->parseEmbeddedContent();
		
		// remove empty elements and join identical siblings if appropriate
		$this->cleanup();
		
		EventHandler::getInstance()->fireAction($this, 'afterProcess');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmbeddedContent() {
		return $this->embeddedContent;
	}
	
	public function addEmbeddedContent($type, array $data) {
		$this->embeddedContent[$type] = $data;
	}
	
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
	
	protected function cleanup() {
		// remove emtpy elements
		foreach ($this->emptyTags as $emptyTag) {
			$elements = [];
			foreach ($this->getDocument()->getElementsByTagName($emptyTag) as $element) {
				$elements[] = $element;
			}
			
			/** @var \DOMElement $element */
			foreach ($elements as $element) {
				if (DOMUtil::isEmpty($element)) {
					DOMUtil::removeNode($element);
				}
			}
		}
		
		// find identical siblings
		foreach ($this->mergeTags as $mergeTag) {
			$elements = [];
			foreach ($this->getDocument()->getElementsByTagName($mergeTag) as $element) {
				$elements[] = $element;
			}
			
			/** @var \DOMElement $element */
			foreach ($elements as $element) {
				$sibling = $element->nextSibling;
				if ($sibling === null) {
					continue;
				}
				
				if ($sibling->nodeName === $mergeTag) {
					while ($sibling->hasChildNodes()) {
						$element->appendChild($sibling->childNodes[0]);
					}
					
					DOMUtil::removeNode($sibling);
				}
			}
		}
	}
}
