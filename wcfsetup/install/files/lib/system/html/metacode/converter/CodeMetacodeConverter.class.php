<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Converts code bbcode into `<pre>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
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
		
		$element->setAttribute('data-file', StringUtil::decodeHTML($file));
		$element->setAttribute('data-highlighter', $highlighter);
		$element->setAttribute('data-line', $line);
		
		$element->appendChild($fragment);
		
		// strip all newline characters, this process requires the element to be part of the DOM,
		// otherwise xpath won't match the text nodes 
		$body = $element->ownerDocument->getElementsByTagName('body')->item(0);
		$body->appendChild($element);
		
		$xpath = new \DOMXPath($element->ownerDocument);
		$replaceNodes = [];
		/** @var \DOMText $textNode */
		foreach ($xpath->query('.//text()', $element) as $textNode) {
			if (mb_strpos($textNode->textContent, "\n") !== false) {
				$replaceNodes[] = $textNode;
			}
		}
		
		/** @var \DOMText $node */
		foreach ($replaceNodes as $node) {
			$newText = preg_replace('~\r?\n~', '', $node->textContent);
			if ($newText !== '') {
				$newNode = $node->ownerDocument->createTextNode($newText);
				$node->parentNode->insertBefore($newNode, $node);
			}
			
			$node->parentNode->removeChild($node);
		}
		
		// remove the element again
		$body->removeChild($element);
		
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
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		// 0-3 attributes
		return (count($attributes) <= 3);
	}
}
