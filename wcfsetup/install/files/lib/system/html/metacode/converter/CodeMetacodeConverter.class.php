<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

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
			case 0:
				break;
			
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
		
		// convert code lines
		$childNodes = DOMUtil::getChildNodes($element);
		/** @var \DOMElement $node */
		foreach ($childNodes as $node) {
			if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName === 'p') {
				DOMUtil::insertAfter($node->ownerDocument->createTextNode("\n\n"), $node);
				
				$brs = $node->getElementsByTagName('br');
				while ($brs->length) {
					$br = $brs->item(0);
					DOMUtil::insertBefore($br->ownerDocument->createTextNode("\n"), $br);
					DOMUtil::removeNode($br);
				}
				
				DOMUtil::removeNode($node, true);
			}
		}
		
		// clear any other elements contained within
		$elements = $element->getElementsByTagName('*');
		while ($elements->length) {
			/** @var \DOMElement $child */
			$child = $elements->item(0);
			if ($child->nodeName === 'a') {
				DOMUtil::insertBefore($child->ownerDocument->createTextNode($child->getAttribute('href')), $child);
				DOMUtil::removeNode($child);
				continue;
			}
			
			DOMUtil::removeNode($child, true);
		}
		
		// trim code block
		$element->textContent = StringUtil::trim($element->textContent);
		
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
