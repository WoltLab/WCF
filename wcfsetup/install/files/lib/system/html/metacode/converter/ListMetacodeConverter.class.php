<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Converts list bbcode into `<ol>`/`<ul>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class ListMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$tagName = 'ul';
		$listType = (!empty($attributes[0])) ? $attributes[0] : 'none';
		if ($listType == 'a' || $listType == 'decimal') {
			$tagName = 'ol';
		}
		
		$element = $fragment->ownerDocument->createElement($tagName);
		$element->appendChild($fragment);
		
		// get all text nodes
		$nodes = [];
		$xpath = new \DOMXPath($element->ownerDocument);
		/** @var \DOMText $node */
		foreach ($xpath->query('.//text()', $element) as $node) {
			if (mb_strpos($node->textContent, '[*]') !== false && !$this->isInsideList($node)) {
				$nodes[] = $node;
			}
		}
		
		// handle empty lists
		if (empty($nodes)) {
			$element->appendChild($element->ownerDocument->createElement('li'));
		}
		else {
			$targetNodes = [];
			foreach ($nodes as $node) {
				$parts = preg_split('~(\[\*\])~', $node->textContent, -1, PREG_SPLIT_DELIM_CAPTURE);
				$parent = $node->parentNode;
				foreach ($parts as $part) {
					switch ($part) {
						case '':
							// ignore
							break;
						
						case '[*]':
							$listItem = $parent->ownerDocument->createElement('woltlab-list-item');
							$parent->insertBefore($listItem, $node);
							$targetNodes[] = $listItem;
							break;
						
						default:
							$textNode = $parent->ownerDocument->createTextNode($part);
							$parent->insertBefore($textNode, $node);
							break;
					}
				}
				
				$parent->removeChild($node);
			}
			
			// split before each target node
			foreach ($targetNodes as $targetNode) {
				DOMUtil::splitParentsUntil($targetNode, $element);
			}
			$ancestors = [];
			foreach ($targetNodes as $targetNode) {
				$ancestors[] = DOMUtil::getParentBefore($targetNode, $element);
			}
			
			$childNodes = [];
			foreach ($element->childNodes as $childNode) $childNodes[] = $childNode;
			
			$listItem = $element->ownerDocument->createElement('li');
			for ($i = 0, $length = count($childNodes); $i < $length; $i++) {
				$childNode = $childNodes[$i];
				if (in_array($childNode, $ancestors, true) && $i !== 0) {
					$element->appendChild($listItem);
					
					// only create a new list item if this isn't the first child node
					$listItem = $element->ownerDocument->createElement('li');
				}
				
				$listItem->appendChild($childNode);
			}
			$element->appendChild($listItem);
			
			// remove marker elements
			$markers = $element->getElementsByTagName('woltlab-list-item');
			while ($markers->length) {
				DOMUtil::removeNode($markers->item(0));
			}
			
			$childNodes = [];
			foreach ($element->childNodes as $childNode) $childNodes[] = $childNode;
			
			// remove <p> and replace it with <br>
			/** @var \DOMElement $childNode */
			foreach ($childNodes as $childNode) {
				/** @var \DOMElement $node */
				foreach ($childNode->childNodes as $node) {
					if ($node->nodeName === 'p') {
						if ($node->childNodes->length && $node->parentNode->lastChild !== $node) {
							DOMUtil::insertAfter($node->ownerDocument->createElement('br'), $node);
						}
						
						DOMUtil::removeNode($node, true);
					}
				}
				
				// check for empty <li> only containing whitespace, this can be a result
				// from the usages of <p> wrapping [list]
				$isEmpty = true;
				foreach ($childNode->childNodes as $node) {
					if ($node->nodeType === XML_TEXT_NODE) {
						if (StringUtil::trim($node->textContent) !== '') {
							$isEmpty = false;
							break;
						}
					}
					else if ($node->nodeType === XML_ELEMENT_NODE) {
						$isEmpty = false;
						break;
					}
				}
				
				// discard the <li> unless it is the only one
				if ($isEmpty && $childNode->parentNode->firstChild !== $childNode) {
					DOMUtil::removeNode($childNode);
				}
			}
			
			// remove trailing whitespaces and <br> from list items
			foreach ($element->childNodes as $childNode) {
				$node = $childNode->lastChild;
				while ($node !== null) {
					if ($node->nodeType === XML_TEXT_NODE) {
						if (StringUtil::trim($node->textContent) !== '') {
							break;
						}
					}
					else if ($node->nodeType === XML_ELEMENT_NODE) {
						if ($node->nodeName === 'p') {
							if ($node->hasChildNodes()) {
								break;
							}
						}
						else if ($node->nodeName !== 'br') {
							break;
						}
					}
					
					$removeNode = $node;
					$node = $node->previousSibling;
					
					DOMUtil::removeNode($removeNode);
				}
			}
			
			// remove the first list item if it is completely empty
			if ($element->firstChild->childNodes->length === 0 && $element->firstChild !== $element->lastChild) {
				DOMUtil::removeNode($element->firstChild);
			}
		}
		
		return $element;
	}
	
	/**
	 * Returns true if provided node is within another list, prevents issues
	 * with nested lists handled in the wrong order.
	 * 
	 * @param       \DOMNode        $node           target node
	 * @return      boolean         true if provided node is within another list
	 */
	protected function isInsideList(\DOMNode $node) {
		/** @var \DOMElement $parent */
		$parent = $node;
		while ($parent = $parent->parentNode) {
			if ($parent->nodeName === 'woltlab-metacode' && $parent->getAttribute('data-name') === 'list') {
				return true;
			}
		}
		
		return false;
	}
}
