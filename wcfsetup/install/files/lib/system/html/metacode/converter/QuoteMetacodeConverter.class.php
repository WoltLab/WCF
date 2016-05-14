<?php
namespace wcf\system\html\metacode\converter;

class QuoteMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('blockquote');
		$element->setAttribute('class', 'quoteBox');
		$element->setAttribute('data-quote-title', (isset($attributes[0])) ? $attributes[0] : '');
		$element->setAttribute('data-quote-url', (isset($attributes[1])) ? $attributes[1] : '');
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		// 0, 1 or 2 attributes
		return (count($attributes) <= 2);
	}
}
