<?php
namespace wcf\system\html\node;
use wcf\system\exception\SystemException;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlNodeProcessor {
	/**
	 * @var	\DOMDocument
	 */
	protected $document;
	
	protected $nodeData = [];
	
	public function load($html) {
		$this->document = new \DOMDocument();
		
		// convert entities as DOMDocument screws them up
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		// ignore all errors when loading the HTML string, because DOMDocument does not
		// provide a proper way to add custom HTML elements (even though explicitly allowed
		// in HTML5) and the input HTML has already been sanitized by HTMLPurifier
		@$this->document->loadHTML($html);
		
		$this->nodeData = [];
	}
	
	public function getHtml() {
		$html = $this->document->saveHTML();
		
		// remove nuisance added by PHP
		$html = preg_replace('~^<!DOCTYPE[^>]+>\s<html><body>~', '', $html);
		$html = preg_replace('~</body></html>$~', '', $html);
		
		$html = mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');
		
		/** @var IHtmlNode $obj */
		foreach ($this->nodeData as $data) {
			$obj = $data['object'];
			$string = $obj->replaceTag($data['data']);
			$html = preg_replace_callback('~<wcfNode-' . $data['identifier'] . '>(?P<content>.*)</wcfNode-' . $data['identifier'] . '>~', function($matches) use ($string) {
				$string = str_replace('<!-- META_CODE_INNER_CONTENT -->', $matches['content'], $string);
				
				return $string;
			}, $html);
			
		}
		
		return $html;
	}
	
	public function getDocument() {
		return $this->document;
	}
	
	public function renameTag(\DOMElement $element, $tagName) {
		$newElement = $this->document->createElement($tagName);
		$element->parentNode->insertBefore($newElement, $element);
		while ($element->hasChildNodes()) {
			$newElement->appendChild($element->firstChild);
		}
		
		$element->parentNode->removeChild($element);
		
		return $newElement;
	}
	
	public function unwrapContent(\DOMElement $element) {
		while ($element->hasChildNodes()) {
			$element->parentNode->insertBefore($element->firstChild, $element);
		}
		
		$element->parentNode->removeChild($element);
	}
	
	public function addNodeData(IHtmlNode $htmlNode, $nodeIdentifier, array $data) {
		$this->nodeData[] = [
			'data' => $data,
			'identifier' => $nodeIdentifier,
			'object' => $htmlNode
		];
	}
	
	protected function invokeHtmlNode(IHtmlNode $htmlNode) {
		$tagName = $htmlNode->getTagName();
		if (empty($tagName)) {
			throw new SystemException("Missing tag name for " . get_class($htmlNode));
		}
		
		$elements = [];
		foreach ($this->getDocument()->getElementsByTagName($tagName) as $element) {
			$elements[] = $element;
		}
		
		if (!empty($elements)) {
			$htmlNode->process($elements, $this);
		}
	}
}
