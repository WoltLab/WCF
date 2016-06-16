<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	3.0
 */
class SpoilerMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('woltlab-spoiler');
		$element->setAttribute('data-label', (!empty($attributes[0])) ? StringUtil::trim($attributes[0]) : '');
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		// 0 or 1 attribute
		return (count($attributes) <= 1);
	}
}
