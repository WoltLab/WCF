<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Provides helper methods to work with PHP's DOM implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class DOMUtil {
	/**
	 * Moves all child nodes from given element into a document fragment.
	 * 
	 * @param	\DOMElement	$element	element
	 * @return	\DOMDocumentFragment		document fragment containing all child nodes from `$element`
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
	 * @param	\DOMNode	$ancestor	ancestor node
	 * @param	\DOMNode	$node		node
	 * @return	boolean		true if `$ancestor` contains the node `$node`
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
	 * Returns a static list of child nodes of provided element.
	 * 
	 * @param       \DOMElement     $element        target element
	 * @return      \DOMNode[]      list of child nodes
	 */
	public static function getChildNodes(\DOMElement $element) {
		$nodes = [];
		foreach ($element->childNodes as $node) {
			$nodes[] = $node;
		}
		
		return $nodes;
	}
	
	/**
	 * Returns the common ancestor of both nodes.
	 * 
	 * @param	\DOMNode		$node1		first node
	 * @param	\DOMNode		$node2		second node
	 * @return	\DOMNode|null	common ancestor or null
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
	 * Returns a non-live collection of elements.
	 * 
	 * @param       (\DOMDocument|\DOMElement)      $context        context element
	 * @param       string                          $tagName        tag name
	 * @return      \DOMElement[]                   list of elements
	 * @throws      SystemException
	 */
	public static function getElements($context, $tagName) {
		if (!($context instanceof \DOMDocument) && !($context instanceof \DOMElement)) {
			throw new SystemException("Expected context to be either of type \\DOMDocument or \\DOMElement.");
		}
		
		$elements = [];
		foreach ($context->getElementsByTagName($tagName) as $element) {
			$elements[] = $element;
		}
		
		return $elements;
	}
	
	/**
	 * Returns the immediate parent element before provided ancestor element. Returns null if
	 * the ancestor element is the direct parent of provided node.
	 * 
	 * @param	\DOMNode		$node		node
	 * @param	\DOMElement		$ancestor	ancestor node
	 * @return	\DOMElement|null	immediate parent element before ancestor element
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
	 * @param	\DOMNode	$node		node
	 * @return	\DOMNode	parent node, can be `\DOMElement` or `\DOMDocument`
	 */
	public static function getParentNode(\DOMNode $node) {
		return $node->parentNode ?: $node->ownerDocument;
	}
	
	/**
	 * Returns all ancestors nodes for given node.
	 * 
	 * @param	\DOMNode	$node		node
	 * @param	boolean		$reverseOrder	reversing the order causes the most top ancestor to appear first
	 * @return	\DOMElement[]	list of ancestor nodes
	 */
	public static function getParents(\DOMNode $node, $reverseOrder = false) {
		$parents = [];
		
		$parent = $node;
		while ($parent = $parent->parentNode) {
			$parents[] = $parent;
		}
		
		return $reverseOrder ? array_reverse($parents) : $parents;
	}
	
	/**
	 * Returns a cloned parent tree that is virtually readonly. In fact it can be
	 * modified, but all changes are non permanent and do not affect the source
	 * document at all.
	 * 
	 * @param       \DOMNode        $node           node
	 * @return      \DOMElement[]   list of parent elements
	 */
	public static function getReadonlyParentTree(\DOMNode $node) {
		$tree = [];
		/** @var \DOMElement $parent */
		foreach (self::getParents($node) as $parent) {
			// do not include <body>, <html> and the document itself
			if ($parent->nodeName === 'body') break;
			
			$tree[] = $parent->cloneNode(false);
		}
		
		return $tree;
	}
	
	/**
	 * Determines the relative position of two nodes to each other.
	 * 
	 * @param	\DOMNode	$node1		first node
	 * @param	\DOMNode	$node2		second node
	 * @return	string
	 */
	public static function getRelativePosition(\DOMNode $node1, \DOMNode $node2) {
		if ($node1->ownerDocument !== $node2->ownerDocument) {
			throw new \InvalidArgumentException("Both nodes must be contained in the same DOM document.");
		}
		
		$nodeList1 = self::getParents($node1, true);
		$nodeList1[] = $node1;
		
		$nodeList2 = self::getParents($node2, true);
		$nodeList2[] = $node2;
		
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
		
		throw new \RuntimeException("Unable to determine relative node position.");
	}
	
	/**
	 * Returns true if there is at least one parent with the provided tag name.
	 * 
	 * @param       \DOMElement     $element        start element
	 * @param       string          $tagName        tag name to match
	 * @return      boolean         
	 */
	public static function hasParent(\DOMElement $element, $tagName) {
		while ($element = $element->parentNode) {
			if ($element->nodeName === $tagName) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Inserts given DOM node after the reference node.
	 * 
	 * @param	\DOMNode 	$node		node
	 * @param	\DOMNode	$refNode	reference node
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
	 * @param	\DOMNode	$node		node
	 * @param	\DOMNode	$refNode	reference node
	 */
	public static function insertBefore(\DOMNode $node, \DOMNode $refNode) {
		self::getParentNode($refNode)->insertBefore($node, $refNode);
	}
	
	/**
	 * Returns true if this node is empty.
	 * 
	 * @param	\DOMNode	$node		node
	 * @return	boolean		true if node is empty
	 */
	public static function isEmpty(\DOMNode $node) {
		if ($node->nodeType === XML_TEXT_NODE) {
			return (StringUtil::trim($node->nodeValue) === '');
		}
		else if ($node->nodeType === XML_ELEMENT_NODE) {
			/** @var \DOMElement $node */
			if (self::isVoidElement($node)) {
				return false;
			}
			else if ($node->hasChildNodes()) {
				for ($i = 0, $length = $node->childNodes->length; $i < $length; $i++) {
					if (!self::isEmpty($node->childNodes->item($i))) {
						return false;
					}
				}
			}
			
			return true;
		}
		
		return true;
	}
	
	/**
	 * Returns true if given node is the first node of its given ancestor.
	 * 
	 * @param	\DOMNode	$node		node
	 * @param	\DOMElement	$ancestor	ancestor element
	 * @return	boolean		true if `$node` is the first node of its given ancestor
	 */
	public static function isFirstNode(\DOMNode $node, \DOMElement $ancestor) {
		if ($node->previousSibling === null) {
			if ($node->parentNode === $ancestor || $node->parentNode->nodeName === 'body') {
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
	 * @param	\DOMNode	$node		node
	 * @param	\DOMElement	$ancestor	ancestor element
	 * @return	boolean		true if `$node` is the last node of its given ancestor
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
	 * Nodes can get partially destroyed in which they're still an
	 * actual DOM node (such as \DOMElement) but almost their entire
	 * body is gone, including the `nodeType` attribute.
	 * 
	 * @param       \DOMNode        $node           node
	 * @return      boolean         true if node has been destroyed
	 */
	public static function isRemoved(\DOMNode $node) {
		return !isset($node->nodeType);
	}
	
	/**
	 * Returns true if provided element is a void element. Void elements are elements
	 * that neither contain content nor have a closing tag, such as `<br>`.
	 * 
	 * @param	\DOMElement	$element	element
	 * @return	boolean	true if provided element is a void element
	 */
	public static function isVoidElement(\DOMElement $element) {
		if (preg_match('~^(area|base|br|col|embed|hr|img|input|keygen|link|menuitem|meta|param|source|track|wbr)$~', $element->nodeName)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Moves all nodes into `$container` until it reaches `$lastElement`. The direction
	 * in which nodes will be considered for moving is determined by the logical position
	 * of `$lastElement`.
	 * 
	 * @param	\DOMElement	$container		destination element
	 * @param	\DOMElement	$lastElement		last element to move
	 * @param	\DOMElement	$commonAncestor		common ancestor of `$container` and `$lastElement`
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
	 * Normalizes an element by joining adjacent text nodes.
	 * 
	 * @param       \DOMElement     $element        target element
	 */
	public static function normalize(\DOMElement $element) {
		$childNodes = self::getChildNodes($element);
		/** @var \DOMNode $lastTextNode */
		$lastTextNode = null;
		foreach ($childNodes as $childNode) {
			if ($childNode->nodeType !== XML_TEXT_NODE) {
				$lastTextNode = null;
				continue;
			}
			
			if ($lastTextNode === null) {
				$lastTextNode = $childNode;
			}
			else {
				// merge with last text node
				$newTextNode = $childNode->ownerDocument->createTextNode($lastTextNode->textContent . $childNode->textContent);
				$element->insertBefore($newTextNode, $lastTextNode);
				
				$element->removeChild($lastTextNode);
				$element->removeChild($childNode);
				
				$lastTextNode = $newTextNode;
			}
		}
	}
	
	/**
	 * Prepends a node to provided element.
	 * 
	 * @param	\DOMNode	$node		node
	 * @param	\DOMElement	$element	target element
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
	 * @param	\DOMNode	$node			target node
	 * @param	boolean		$preserveChildNodes	preserve child nodes, only supported for elements
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
	 * @param	\DOMElement	$oldElement		old element
	 * @param	\DOMElement	$newElement		new element
	 * @param	boolean		$preserveChildNodes	true if child nodes should be moved, otherwise they'll be implicitly removed
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
	 * @param	\DOMNode	$node		reference node
	 * @param	\DOMElement	$ancestor	ancestor element that should not be split
	 * @param	boolean		$splitBefore	true if nodes before `$node` should be moved into a new node, false to split nodes after `$node`
	 * @return	\DOMNode	parent node containing `$node`, direct child of `$ancestor`
	 */
	public static function splitParentsUntil(\DOMNode $node, \DOMElement $ancestor, $splitBefore = true) {
		if (!self::contains($ancestor, $node)) {
			throw new \InvalidArgumentException("Node is not contained in ancestor node.");
		}
		
		// clone the parent node right "below" `$ancestor`
		$cloneNode = self::getParentBefore($node, $ancestor);
		
		if ($splitBefore) {
			if ($cloneNode === null) {
				// target node is already a direct descendant of the ancestor
				// node, no need to split anything
				return $node;
			}
			else if (self::isFirstNode($node, $cloneNode)) {
				// target node is at the very start, we can safely move the
				// entire parent node around
				return $cloneNode;
			}
			
			$currentNode = $node;
			while (($parent = $currentNode->parentNode) !== $ancestor) {
				/** @var \DOMElement $newNode */
				$newNode = $parent->cloneNode();
				self::insertBefore($newNode, $parent);
				
				while ($currentNode->previousSibling) {
					self::prepend($currentNode->previousSibling, $newNode);
				}
				
				$currentNode = $parent;
			}
		}
		else {
			if ($cloneNode === null) {
				// target node is already a direct descendant of the ancestor
				// node, no need to split anything
				return $node;
			}
			else if (self::isLastNode($node, $cloneNode)) {
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
	
	/**
	 * Forbid creation of DOMUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
