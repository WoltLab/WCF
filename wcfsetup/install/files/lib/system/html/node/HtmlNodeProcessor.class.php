<?php
namespace wcf\system\html\node;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlNodeProcessor {
	/**
	 * @var	\DOMDocument
	 */
	protected $document;
	
	public function load($html) {
		$this->document = new \DOMDocument();
		
		// convert entities as DOMDocument screws them up
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		// ignore all errors when loading the HTML string, because DOMDocument does not
		// provide a proper way to add custom HTML elements (even though explicitly allowed
		// in HTML5) and the input HTML has already been sanitized by HTMLPurifier
		@$this->document->loadHTML($html);
	}
	
	public function getHtml() {
		$html = $this->document->saveHTML();
		
		// remove nuisance added by PHP
		$html = preg_replace('~^<!DOCTYPE[^>]+>\s<html><body>~', '', $html);
		$html = preg_replace('~</body></html>$~', '', $html);
		
		$html = mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');
		
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
}
