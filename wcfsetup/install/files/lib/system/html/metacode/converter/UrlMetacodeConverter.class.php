<?php
namespace wcf\system\html\metacode\converter;

/**
 * Converts url bbcode into `<a>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class UrlMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('a');
		
		$href = (!empty($attributes[0])) ? $attributes[0] : '';
		if (empty($href)) {
			$href = $fragment->textContent;
		}
		
		$element->setAttribute('href', $href);
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		if (count($attributes) > 1) {
			return false;
		}
		
		return true;
	}
}
