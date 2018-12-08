<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Converts html bbcode into `<pre class="woltlabHtml">`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class HtmlMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('pre');
		$element->setAttribute('class', 'woltlabHtml');
		$element->appendChild($fragment);
		
		// convert code lines
		$childNodes = DOMUtil::getChildNodes($element);
		/** @var \DOMElement $node */
		foreach ($childNodes as $node) {
			if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName === 'p') {
				DOMUtil::insertAfter($node->ownerDocument->createTextNode("\n"), $node);
				
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
		$content = StringUtil::trim($element->textContent);
		$element->nodeValue = '';
		$element->appendChild($element->ownerDocument->createTextNode($content));
		
		return $element;
	}
}
