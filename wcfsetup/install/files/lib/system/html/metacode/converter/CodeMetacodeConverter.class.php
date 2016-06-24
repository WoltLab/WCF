<?php
namespace wcf\system\html\metacode\converter;

/**
 * Converts code bbcode into `<pre>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class CodeMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('pre');
		
		$line = 1;
		$highlighter = $file = '';
		
		switch (count($attributes)) {
			case 1:
				if (is_numeric($attributes[0])) {
					$line = intval($attributes[0]);
				}
				else if (mb_strpos($attributes[0], '.') === false) {
					$highlighter = $attributes[0];
				}
				else {
					$file = $attributes[0];
				}
				break;
			
			case 2:
				if (is_numeric($attributes[0])) {
					$line = intval($attributes[0]);
					if (mb_strpos($attributes[1], '.') === false) {
						$highlighter = $attributes[1];
					}
					else {
						$file = $attributes[1];
					}
				}
				else {
					$highlighter = $attributes[0];
					$file = $attributes[1];
				}
				break;
			
			default:
				$highlighter = $attributes[0];
				$line = intval($attributes[1]);
				$file = $attributes[2];
				break;
		}
		
		$element->setAttribute('data-file', $file);
		$element->setAttribute('data-highlighter', $highlighter);
		$element->setAttribute('data-line', $line);
		
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		// 0-3 attributes
		return (count($attributes) <= 3);
	}
}
