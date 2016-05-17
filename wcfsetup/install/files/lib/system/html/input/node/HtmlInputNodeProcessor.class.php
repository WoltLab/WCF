<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeProcessor extends HtmlNodeProcessor {
	// TODO: this should include other tags
	protected $emptyTags = ['em', 'strong', 'u'];
	
	// TODO: this should include other tags
	protected $mergeTags = ['em', 'strong', 'u'];
	
	public function process() {
		// process metacode markers first
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());
		
		// handle static converters
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());
		
		// remove empty elements and join identical siblings if appropriate
		$this->cleanup();
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
