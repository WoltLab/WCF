<?php
namespace wcf\system\html\metacode\converter;

/**
 * TOOD documentation
 * @since	2.2
 */
class SizeMetacodeConverter extends AbstractMetacodeConverter {
	protected $sizes = [8, 10, 12, 14, 18, 24, 36];
	
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('woltlab-size');
		$element->setAttribute('class', 'woltlab-size-' . $attributes[0]);
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		if (count($attributes) !== 1) {
			return false;
		}
		
		// validates if size is allowed
		if (!in_array($attributes[0], $this->sizes)) {
			return false;
		}
		
		return true;
	}
}
