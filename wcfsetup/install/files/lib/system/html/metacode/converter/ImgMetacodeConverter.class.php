<?php
namespace wcf\system\html\metacode\converter;

/**
 * Converts img bbcode into `<img>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2017 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class ImgMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('img');
		$element->setAttribute('src', $attributes[0]);
		
		if (isset($attributes[1]) && in_array($attributes[1], ['left', 'right'])) {
			$element->setAttribute('class', 'messageFloatObject'.ucfirst($attributes[1]));
		}
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		$count = count($attributes);
		return ($count > 0 && $count < 4);
	}
}
