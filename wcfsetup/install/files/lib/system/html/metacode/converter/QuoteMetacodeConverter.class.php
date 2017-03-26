<?php
namespace wcf\system\html\metacode\converter;

/**
 * Converts quote bbcode into `<woltlab-quote>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2017 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class QuoteMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('woltlab-quote');
		$element->setAttribute('data-author', isset($attributes[0]) ? $attributes[0] : '');
		$element->setAttribute('data-link', isset($attributes[1]) ? $attributes[1] : '');
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
