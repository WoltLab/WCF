<?php
namespace wcf\system\html\metacode\converter;

/**
 * Converts color bbcode into `<woltlab-color>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class ColorMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('woltlab-color');
		$element->setAttribute('class', 'woltlab-color-' . strtoupper(substr($attributes[0], 1)));
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
		
		// validates if code is a valid (short) HEX color code
		if (!preg_match('~^#[A-F0-9]{3}(?:[A-F0-9]{3})?$~i', $attributes[0])) {
			return false;
		}
		
		return true;
	}
}
