<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Provides helper methods to work with PHP's DOM implementation.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.wcf
 * @subpackage  util
 * @category    Community Framework
 */
final class DOMUtil {
	/**
	 * Moves all child nodes from given element into a document fragment.
	 * 
	 * @param       \DOMElement     $element        element
	 * @return      \DOMDocumentFragment            document fragment containing all child nodes from `$element`
	 */
	public static function childNodesToFragment(\DOMElement $element) {
		$fragment = $element->ownerDocument->createDocumentFragment();
		
		while ($element->hasChildNodes()) {
			$fragment->appendChild($element->childNodes->item(0));
		}
		
		return $fragment;
	}
	
	/**
	 * Returns true if `$ancestor` contains the node `$node`.
	 * 
	 * @param       \DOMNode        $ancestor       ancestor node
	 * @param       \DOMNode        $node           node
	 * @return      boolean         true if `$ancestor` contains the node `$node`
	 */
	public static function contains(\DOMNode $ancestor, \DOMNode $node) {
		// nodes cannot contain themselves
		if ($ancestor === $node) {
			return false;
		}
		
		// text nodes cannot contain any other nodes
		if ($ancestor->nodeType === XML_TEXT_NODE) {
			return false;
		}
		
		$parent = $node;
		while ($parent = $parent->parentNode) {
			if ($parent === $ancestor) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the common ancestor of both nodes.
	 * 
	 * @param       \DOMNode                $node1          first node
	 * @param       \DOMNode                $node2          second node
	 * @return      \DOMElement|null        common ancestor or null
	 */
	public static function getCommonAncestor(\DOMNode $node1, \DOMNode $node2) {
		// abort if both elements share a common element or are both direct descendants
		// of the same document
		if ($node1->parentNode === $node2->parentNode) {
			return $node1->parentNode;
		}
		
		// collect the list of all direct ancestors of `$node1`
		$parents = self::getParents($node1);
		
		// compare each ancestor of `$node2` to the known list of parents of `$node1`
		$parent = $node2;
		while ($parent = $parent->parentNode) {
			// requires strict type check
			if (in_array($parent, $parents, true)) {
				return $parent;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the immediate parent element before provided ancenstor element. Returns null if
	 * the ancestor element is the direct parent of provided node.
	 * 
	 * @param       \DOMNode                $node           node
	 * @param       \DOMElement             $ancestor       ancestor node
	 * @return      \DOMElement|null        immediate parent element before ancestor element
	 */
	public static function getParentBefore(\DOMNode $node, \DOMElement $ancestor) {
		if ($node->parentNode === $ancestor) {
			return null;
		}
		
		$parents = self::getParents($node);
		for ($i = count($parents) - 1; $i >= 0; $i--) {
			if ($parents[$i] === $ancestor) {
				return $parents[$i - 1];
			}
		}
		
		throw new \InvalidArgumentException("Provided node is a not a descendant of ancestor element.");
	}
	
	/**
	 * Returns the parent node of given node.
	 *
	 * @param       \DOMNode        $node           node
	 * @return      \DOMNode        parent node, can be `\DOMElement` or `\DOMDocument`
	 */
	public static function getParentNode(\DOMNode $node) {
		return ($node->parentNode) ?: $node->ownerDocument;
	}
	
	/**
	 * Returns all ancestors nodes for given node.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       boolean         $reverseOrder   reversing the order causes the most top ancestor to appear first
	 * @return      \DOMElement[]   list of ancestor nodes
	 */
	public static function getParents(\DOMNode $node, $reverseOrder = false) {
		$parents = [];
		
		$parent = $node;
		while ($parent = $parent->parentNode) {
			$parents[] = $parent;
		}
		
		return ($reverseOrder) ? array_reverse($parents) : $parents;
	}
	
	/**
	 * Determines the relative position of two nodes to each other.
	 * 
	 * @param       \DOMNode        $node1          first node
	 * @param       \DOMNode        $node2          second node
	 * @return      string
	 */
	public static function getRelativePosition(\DOMNode $node1, \DOMNode $node2) {
		if ($node1->ownerDocument !== $node2->ownerDocument) {
			throw new \InvalidArgumentException("Both nodes must be contained in the same DOM document.");
		}
		
		$nodeList1 = self::getParents($node1, true);
		$nodeList1[] = $node1;
		
		$nodeList2 = self::getParents($node2, true);
		$nodeList2[] = $node2;
		
		$commonAncestor = null;
		$i = 0;
		while ($nodeList1[$i] === $nodeList2[$i]) {
			$i++;
		}
		
		// check if parent of node 2 appears before parent of node 1
		$previousSibling = $nodeList1[$i];
		while ($previousSibling = $previousSibling->previousSibling) {
			if ($previousSibling === $nodeList2[$i]) {
				return 'before';
			}
		}
		
		$nextSibling = $nodeList1[$i];
		while ($nextSibling = $nextSibling->nextSibling) {
			if ($nextSibling === $nodeList2[$i]) {
				return 'after';
			}
		}
		
		throw new SystemException("Unable to determine relative node position.");
	}
	
	/**
	 * Inserts given DOM node after the reference node.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       \DOMNode        $refNode        reference node
	 */
	public static function insertAfter(\DOMNode $node, \DOMNode $refNode) {
		if ($refNode->nextSibling) {
			self::insertBefore($node, $refNode->nextSibling);
		}
		else {
			self::getParentNode($refNode)->appendChild($node);
		}
	}
	
	/**
	 * Inserts given node before the reference node.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       \DOMNode        $refNode        reference node
	 */
	public static function insertBefore(\DOMNode $node, \DOMNode $refNode) {
		self::getParentNode($refNode)->insertBefore($node, $refNode);
	}
	
	/**
	 * Returns true if given node is the first node of its given ancestor.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       \DOMElement     $ancestor       ancestor element
	 * @return      boolean         true if `$node` is the first node of its given ancestor
	 */
	public static function isFirstNode(\DOMNode $node, \DOMElement $ancestor) {
		if ($node->previousSibling === null) {
			if ($node->previousSibling === null) {
				throw new \InvalidArgumentException("Provided node is a not a descendant of ancestor element.");
			}
			else if ($node->parentNode === $ancestor || $node->parentNode->nodeName === 'body') {
				return true;
			}
			else {
				return self::isFirstNode($node->parentNode, $ancestor);
			}
		}
		else if ($node->parentNode->nodeName === 'body') {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if given node is the last node of its given ancestor.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       \DOMElement     $ancestor       ancestor element
	 * @return      boolean         true if `$node` is the last node of its given ancestor
	 */
	public static function isLastNode(\DOMNode $node, \DOMElement $ancestor) {
		if ($node->nextSibling === null) {
			if ($node->parentNode === null) {
				throw new \InvalidArgumentException("Provided node is a not a descendant of ancestor element.");
			}
			else if ($node->parentNode === $ancestor || $node->parentNode->nodeName === 'body') {
				return true;
			}
			else {
				return self::isLastNode($node->parentNode, $ancestor);
			}
		}
		else if ($node->parentNode->nodeName === 'body') {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Moves all nodes into `$container` until it reaches `$lastElement`. The direction
	 * in which nodes will be considered for moving is determined by the logical position
	 * of `$lastElement`.
	 * 
	 * @param       \DOMElement     $container              destination element
	 * @param       \DOMElement     $lastElement            last element to move
	 * @param       \DOMElement     $commonAncestor         common ancestor of `$container` and `$lastElement`
	 */
	public static function moveNodesInto(\DOMElement $container, \DOMElement $lastElement, \DOMElement $commonAncestor) {
		if (!self::contains($commonAncestor, $container)) {
			throw new \InvalidArgumentException("The container element must be a child of the common ancestor element.");
		}
		else if ($lastElement->parentNode !== $commonAncestor) {
			throw new \InvalidArgumentException("The last element must be a direct child of the common ancestor element.");
		}
		
		$relativePosition = self::getRelativePosition($container, $lastElement);
		
		// move everything that is logically after `$container` but within
		// `$commonAncestor` into `$container` until `$lastElement` has been moved
		$element = $container;
		do {
			if ($relativePosition === 'before') {
				while ($sibling = $element->previousSibling) {
					self::prepend($sibling, $container);
					if ($sibling === $lastElement) {
						return;
					}
				}
			}
			else {
				while ($sibling = $element->nextSibling) {
					$container->appendChild($sibling);
					if ($sibling === $lastElement) {
						return;
					}
				}
			}
			
			$element = $element->parentNode;
		}
		while ($element !== $commonAncestor);
	}
	
	/**
	 * Prepends a node to provided element.
	 * 
	 * @param       \DOMNode        $node           node
	 * @param       \DOMElement     $element        target element
	 */
	public static function prepend(\DOMNode $node, \DOMElement $element) {
		if ($element->firstChild === null) {
			$element->appendChild($node);
		}
		else {
			$element->insertBefore($node, $element->firstChild);
		}
	}
	
	/**
	 * Removes a node, optionally preserves the child nodes if `$node` is an element.
	 * 
	 * @param       \DOMNode        $node                   target node
	 * @param       boolean         $preserveChildNodes     preserve child nodes, only supported for elements
	 */
	public static function removeNode(\DOMNode $node, $preserveChildNodes = false) {
		if ($preserveChildNodes) {
			if (!($node instanceof \DOMElement)) {
				throw new \InvalidArgumentException("Preserving child nodes is only supported for DOMElement.");
			}
			
			while ($node->hasChildNodes()) {
				self::insertBefore($node->childNodes->item(0), $node);
			}
		}
		
		self::getParentNode($node)->removeChild($node);
	}
	
	/**
	 * Replaces a DOM element with another, preserving all child nodes by default.
	 * 
	 * @param       \DOMElement     $oldElement             old element
	 * @param       \DOMElement     $newElement             new element
	 * @param       boolean         $preserveChildNodes     true if child nodes should be moved, otherwise they'll be implicitly removed
	 */
	public static function replaceElement(\DOMElement $oldElement, \DOMElement $newElement, $preserveChildNodes = true) {
		self::insertBefore($newElement, $oldElement);
		
		// move all child nodes
		if ($preserveChildNodes) {
			while ($oldElement->hasChildNodes()) {
				$newElement->appendChild($oldElement->childNodes->item(0));
			}
		}
		
		// remove old element
		self::getParentNode($oldElement)->removeChild($oldElement);
	}
	
	/**
	 * Splits all parent nodes until `$ancestor` and moved other nodes after/before
	 * (determined by `$splitBefore`) into the newly created nodes. This allows
	 * extraction of DOM parts while preserving nesting for both the extracted nodes
	 * and the remaining siblings.
	 * 
	 * @param       \DOMNode        $node           reference node
	 * @param       \DOMElement     $ancestor       ancestor element that should not be split
	 * @param       boolean         $splitBefore    true if nodes before `$node` should be moved into a new node, false to split nodes after `$node`
	 * @return      \DOMElement     parent node containing `$node`, direct child of `$ancestor`
	 */
	public static function splitParentsUntil(\DOMNode $node, \DOMElement $ancestor, $splitBefore = true) {
		if (!self::contains($ancestor, $node)) {
			throw new \InvalidArgumentException("Node is not contained in ancestor node.");
		}
		
		// clone the parent node right "below" `$ancestor`
		$cloneNode = self::getParentBefore($node, $ancestor);
		
		if ($splitBefore) {
			if (self::isFirstNode($node, $cloneNode)) {
				// target node is at the very start, we can safely move the
				// entire parent node around
				return $cloneNode;
			}
			
			$currentNode = $node;
			while (($parent = $currentNode->parentNode) !== $ancestor) {
				$newNode = $parent->cloneNode();
				self::insertBefore($newNode, $parent);
				
				while ($currentNode->previousSibling) {
					$newNode->appendChild($currentNode->previousSibling);
				}
				
				$currentNode = $parent;
			}
		}
		else {
			if (self::isLastNode($node, $cloneNode)) {
				// target node is at the very end, we can safely move the
				// entire parent node around
				return $cloneNode;
			}
			
			$currentNode = $node;
			while (($parent = $currentNode->parentNode) !== $ancestor) {
				$newNode = $parent->cloneNode();
				self::insertAfter($newNode, $parent);
				
				while ($currentNode->nextSibling) {
					$newNode->appendChild($currentNode->nextSibling);
				}
				
				$currentNode = $parent;
			}
		}
		
		return self::getParentBefore($node, $ancestor);
	}
	
	private function __construct() { }
}
