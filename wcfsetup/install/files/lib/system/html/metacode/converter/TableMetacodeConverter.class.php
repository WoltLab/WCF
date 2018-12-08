<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Converts table bbcodes into `<table>`, `<tr>` and `<td>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class TableMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('table');
		$tbody = $fragment->ownerDocument->createElement('tbody');
		$element->appendChild($tbody);
		$tbody->appendChild($fragment);
		
		// get all table rows
		$rows = [];
		$nodes = $tbody->getElementsByTagName('woltlab-metacode');
		/** @var \DOMElement $node */
		foreach ($nodes as $node) {
			if ($node->getAttribute('data-name') === 'tr' && !$this->isInsideTable($node)) {
				$rows[] = $node;
			}
		}
		
		// fix markup for table rows
		/** @var \DOMElement $row */
		foreach ($rows as $row) {
			if ($row->parentNode !== $tbody) {
				$parent = DOMUtil::getParentBefore($row, $tbody);
				$tbody->insertBefore($row, $parent);
			}
			
			DOMUtil::replaceElement($row, $row->ownerDocument->createElement('tr'));
		}
		
		// drop everything except for <tr> elements
		$childNodes = DOMUtil::getChildNodes($tbody);
		foreach ($childNodes as $childNode) {
			if ($childNode->nodeType === XML_ELEMENT_NODE && $childNode->nodeName === 'tr') {
				continue;
			}
			
			DOMUtil::removeNode($childNode);
		}
		
		// get columns for each tr
		/** @var \DOMElement $childNode */
		foreach ($tbody->childNodes as $childNode) {
			$this->handleRow($childNode);
		}
		
		return $element;
	}
	
	/**
	 * Processes the rows of the table.
	 * 
	 * @param	\DOMElement	$row
	 */
	protected function handleRow(\DOMElement $row) {
		// get all table columns
		$cols = [];
		$nodes = $row->getElementsByTagName('woltlab-metacode');
		/** @var \DOMElement $node */
		foreach ($nodes as $node) {
			if ($node->getAttribute('data-name') === 'td' && !$this->isInsideTable($node)) {
				$cols[] = $node;
			}
		}
		
		// move tds
		/** @var \DOMElement $col */
		foreach ($cols as $col) {
			if (false && $col->parentNode !== $row) {
				$parent = DOMUtil::getParentBefore($col, $row);
				$row->insertBefore($col, $parent);
			}
			
			DOMUtil::replaceElement($col, $row->ownerDocument->createElement('td'));
		}
		
		// drop everything except for <td> elements and removing
		// <p> inside columns
		$childNodes = DOMUtil::getChildNodes($row);
		/** @var \DOMElement $childNode */
		foreach ($childNodes as $childNode) {
			if ($childNode->nodeType === XML_ELEMENT_NODE && $childNode->nodeName === 'td') {
				// convert <p>...</p> to ...<br><br>
				$nodes = DOMUtil::getChildNodes($childNode);
				/** @var \DOMElement $node */
				foreach ($nodes as $node) {
					if ($node->nodeName === 'p') {
						for ($i = 0; $i < 2; $i++) {
							DOMUtil::insertAfter($node->ownerDocument->createElement('br'), $node);
						}
						
						DOMUtil::removeNode($node, true);
					}
				}
				
				// removing leading whitespace / <br>
				$nodes = DOMUtil::getChildNodes($childNode);
				foreach ($nodes as $node) {
					if ($node->nodeType === XML_TEXT_NODE) {
						if (StringUtil::trim($node->textContent) !== '') {
							break;
						}
					}
					else if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'br') {
						break;
					}
					
					DOMUtil::removeNode($node);
				}
				
				// removing trailing whitespace / <br>
				$nodes = DOMUtil::getChildNodes($childNode);
				$i = count($nodes);
				while ($i--) {
					$node = $nodes[$i];
					if ($node->nodeType === XML_TEXT_NODE) {
						if (StringUtil::trim($node->textContent) !== '') {
							break;
						}
					}
					else if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'br') {
						break;
					}
					
					DOMUtil::removeNode($node);
				}
				
				continue;
			}
			
			DOMUtil::removeNode($childNode);
		}
	}
	
	/**
	 * Returns true if provided node is within another table, prevents issues
	 * with nested tables handled in the wrong order.
	 *
	 * @param       \DOMNode        $node           target node
	 * @return      boolean         true if provided node is within another table
	 */
	protected function isInsideTable(\DOMNode $node) {
		/** @var \DOMElement $parent */
		$parent = $node;
		while ($parent = $parent->parentNode) {
			if ($parent->nodeName === 'woltlab-metacode' && $parent->getAttribute('data-name') === 'table') {
				return true;
			}
		}
		
		return false;
	}
}
