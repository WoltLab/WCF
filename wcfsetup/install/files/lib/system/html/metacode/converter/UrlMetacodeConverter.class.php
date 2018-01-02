<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\StringUtil;

/**
 * Converts url bbcode into `<a>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2017 WoltLab GmbH
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
		
		// check if the link is empty, use the href value instead
		$useHrefAsValue = false;
		if ($fragment->childNodes->length === 0) {
			$useHrefAsValue = true;
		}
		else if ($fragment->childNodes->length === 1) {
			$node = $fragment->childNodes->item(0);
			if ($node->nodeType === XML_TEXT_NODE && StringUtil::trim($node->textContent) === '') {
				$useHrefAsValue = true;
			}
		}
		
		if ($useHrefAsValue) {
			if ($fragment->childNodes->length === 1) {
				$fragment->removeChild($fragment->childNodes->item(0));
			}
			
			$fragment->appendChild($fragment->ownerDocument->createTextNode($href));
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
