<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Transforms bbcode markers into the custom HTML element `<woltlab-metacode>`. This process
 * outputs well-formed markup with proper element nesting.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Html\Input\Node
 * @since	3.0
 */
class HtmlInputNodeWoltlabMetacodeMarker extends AbstractHtmlInputNode {
	/**
	 * list of bbcodes that represent block elements
	 * @var	string[]
	 */
	public $blockElements = [];
	
	/**
	 * list of bbcodes that represent source code elements
	 * @var	string[]
	 */
	public $sourceElements = [];
	
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-metacode-marker';
	
	/**
	 * HtmlInputNodeWoltlabMetacodeMarker constructor.
	 */
	public function __construct() {
		$this->blockElements = HtmlBBCodeParser::getInstance()->getBlockBBCodes();
		$this->sourceElements = HtmlBBCodeParser::getInstance()->getSourceBBCodes();
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		// collect pairs
		$pairs = $this->buildPairs($elements);
		
		// validate pairs and remove items that lack an opening/closing element
		$pairs = $this->validatePairs($pairs);
		
		$pairs = $this->revertMarkerInsideCodeBlocks($pairs);
		
		// group pairs by tag name
		$groups = $this->groupPairsByName($pairs);
		
		// convert pairs into HTML or metacode
		$this->convertGroups($groups);
	}
	
	/**
	 * Transforms bbcode markers inside source code elements into their plain bbcode representation.
	 * 
	 * @param	array		$pairs		list of bbcode marker pairs
	 * @return	array		filtered list of bbcode marker pairs
	 */
	protected function revertMarkerInsideCodeBlocks(array $pairs) {
		$isInsideCode = function(\DOMElement $element) {
			$parent = $element;
			while ($parent = $parent->parentNode) {
				$nodeName = $parent->nodeName;
				
				if ($nodeName === 'code' || $nodeName === 'kbd' || $nodeName === 'pre') {
					return true;
				}
				else if ($nodeName === 'woltlab-metacode') {
					$name = $parent->getAttribute('data-name');
					if ($name === 'code' || $name === 'tt') {
						return true;
					}
				}
			}
			
			return false;
		};
		
		foreach ($pairs as $uuid => $pair) {
			if ($isInsideCode($pair['open']) || $isInsideCode($pair['close'])) {
				$this->convertToBBCode($pair);
				
				unset($pairs[$uuid]);
			}
		}
		
		return $pairs;
	}
	
	/**
	 * Builds the list of paired bbcode markers.
	 * 
	 * @param	\DOMElement[]	$elements	list of marker elements
	 * @return	array		list of paired bbcode markers
	 */
	protected function buildPairs(array $elements) {
		$pairs = [];
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$attributes = $element->getAttribute('data-attributes');
			$name = $element->getAttribute('data-name');
			$uuid = $element->getAttribute('data-uuid');
			
			if (!isset($pairs[$uuid])) {
				$pairs[$uuid] = [
					'attributes' => [],
					'close' => null,
					'name' => '',
					'open' => null
				];
			}
			
			if ($name) {
				$pairs[$uuid]['attributes'] = $attributes;
				$pairs[$uuid]['name'] = $name;
				$pairs[$uuid]['open'] = $element;
			}
			else {
				$pairs[$uuid]['close'] = $element;
			}
		}
		
		return $pairs;
	}
	
	/**
	 * Validates bbcode marker pairs to include both an opening and closing element.
	 * 
	 * @param	array		$pairs		list of paired bbcode markers
	 * @return	array		filtered list of paired bbcode markers
	 */
	protected function validatePairs(array $pairs) {
		foreach ($pairs as $uuid => $data) {
			if ($data['close'] === null) {
				DOMUtil::removeNode($data['open']);
			}
			else if ($data['open'] === null) {
				DOMUtil::removeNode($data['close']);
			}
			else {
				continue;
			}
			
			unset($pairs[$uuid]);
		}
		
		return $pairs;
	}
	
	/**
	 * Groups bbcode marker pairs by their common bbcode identifier.
	 * 
	 * @param	array		$pairs		list of paired bbcode markers
	 * @return	array		grouped list of bbcode marker pairs
	 */
	protected function groupPairsByName(array $pairs) {
		$groups = [];
		foreach ($pairs as $uuid => $data) {
			$name = $data['name'];
			
			if (!isset($groups[$name])) {
				$groups[$name] = [];
			}
			
			$groups[$name][] = [
				'attributes' => $data['attributes'],
				'close' => $data['close'],
				'open' => $data['open']
			];
		}
		
		return $groups;
	}
	
	/**
	 * Converts bbcode marker pairs into block- or inline-elements.
	 * 
	 * @param	array		$groups		grouped list of bbcode marker pairs
	 */
	protected function convertGroups(array $groups) {
		// process source elements first
		foreach ($this->sourceElements as $name) {
			if (in_array($name, $this->blockElements)) {
				if (isset($groups[$name])) {
					for ($i = 0, $length = count($groups[$name]); $i < $length; $i++) {
						$data = $groups[$name][$i];
						$this->convertBlockElement($name, $data['open'], $data['close'], $data['attributes']);
					}
					
					unset($groups[$name]);
				}
			}
			else {
				if (isset($groups[$name])) {
					for ($i = 0, $length = count($groups[$name]); $i < $length; $i++) {
						$data = $groups[$name][$i];
						$this->convertInlineElement($name, $data['open'], $data['close'], $data['attributes']);
					}
					
					unset($groups[$name]);
				}
			}
		}
		
		// remaining block elements
		foreach ($this->blockElements as $name) {
			if (isset($groups[$name])) {
				for ($i = 0, $length = count($groups[$name]); $i < $length; $i++) {
					$data = $groups[$name][$i];
					$this->convertBlockElement($name, $data['open'], $data['close'], $data['attributes']);
				}
				
				unset($groups[$name]);
			}
		}
		
		// treat remaining elements as inline elements
		foreach ($groups as $name => $pairs) {
			for ($i = 0, $length = count($pairs); $i < $length; $i++) {
				$data = $pairs[$i];
				$this->convertInlineElement($name, $data['open'], $data['close'], $data['attributes']);
			}
		}
	}
	
	/**
	 * Converts a block-level bbcode marker pair.
	 * 
	 * @param	string		$name		bbcode identifier
	 * @param	\DOMElement	$start		start node
	 * @param	\DOMElement	$end		end node
	 * @param	string		$attributes	encoded attribute string
	 */
	protected function convertBlockElement($name, $start, $end, $attributes) {
		// we need to ensure proper nesting, block elements are not allowed to
		// be placed inside paragraphs, but being a direct child of another block
		// element is completely fine
		$parent = $start;
		do {
			$parent = $parent->parentNode;
		}
		while ($parent->nodeName === 'p' || !$this->isBlockElement($parent));
		
		$element = DOMUtil::splitParentsUntil($start, $parent);
		DOMUtil::insertBefore($start, $element);
		
		$commonAncestor = DOMUtil::getCommonAncestor($start, $end);
		$lastElement = DOMUtil::splitParentsUntil($end, $commonAncestor, false);
		
		$container = $start->ownerDocument->createElement('woltlab-metacode');
		$container->setAttribute('data-name', $name);
		$container->setAttribute('data-attributes', $attributes);
		
		DOMUtil::insertAfter($container, $start);
		DOMUtil::removeNode($start);
		
		DOMUtil::moveNodesInto($container, $lastElement, $commonAncestor);
		
		DOMUtil::removeNode($end);
	}
	
	/**
	 * Converts an inline bbcode marker pair.
	 * 
	 * @param	string		$name		bbcode identifier
	 * @param	\DOMElement	$start		start node
	 * @param	\DOMElement	$end		end node
	 * @param	string		$attributes	encoded attribute string
	 */
	protected function convertInlineElement($name, $start, $end, $attributes) {
		if ($start->parentNode === $end->parentNode) {
			$this->wrapContent($name, $attributes, $start, $end);
			
			DOMUtil::removeNode($start);
			DOMUtil::removeNode($end);
		}
		else {
			$commonAncestor = DOMUtil::getCommonAncestor($start, $end);
			$endAncestor = DOMUtil::getParentBefore($end, $commonAncestor);
			
			$element = $this->wrapContent($name, $attributes, $start, null);
			DOMUtil::removeNode($start);
			
			$element = DOMUtil::getParentBefore($element, $commonAncestor);
			while ($element = $element->nextSibling) {
				if ($element->nodeType === XML_TEXT_NODE) {
					// ignore text nodes between tags
					continue;
				}
				
				if ($element !== $endAncestor) {
					if ($this->isBlockElement($element)) {
						$this->wrapContent($name, $attributes, $element->firstChild, null);
					}
					else {
						$this->wrapContent($name, $attributes, $element, null);
					}
				}
				else {
					$this->wrapContent($name, $attributes, null, $end);
					
					DOMUtil::removeNode($end);
					break;
				}
			}
		}
	}
	
	/**
	 * Wraps a sequence of nodes using a newly created element. If `$startNode` is `null` the end
	 * node and all previous siblings will be added to the element. The reverse takes place if
	 * `$endNode` is `null`.
	 * 
	 * @param	string			$name		element tag name
	 * @param	string			$attributes	encoded attribute string
	 * @param	\DOMElement|null	$startNode	first node to wrap
	 * @param	\DOMElement|null	$endNode	last node to wrap
	 * @return	\DOMElement		newly created element
	 */
	protected function wrapContent($name, $attributes, $startNode, $endNode) {
		if ($startNode === null && $endNode === null) {
			throw new \InvalidArgumentException("Must provide an existing element for start node or end node, both cannot be null.");
		}
		
		$element = ($startNode) ? $startNode->ownerDocument->createElement('woltlab-metacode') : $endNode->ownerDocument->createElement('woltlab-metacode');
		$element->setAttribute('data-name', $name);
		$element->setAttribute('data-attributes', $attributes);
		
		if ($startNode) {
			DOMUtil::insertBefore($element, $startNode);
			
			while ($sibling = $element->nextSibling) {
				$element->appendChild($sibling);
				
				if ($sibling === $endNode) {
					break;
				}
			}
		}
		else {
			DOMUtil::insertAfter($element, $endNode);
			
			while ($sibling = $element->previousSibling) {
				DOMUtil::prepend($sibling, $element);
				
				if ($sibling === $startNode) {
					break;
				}
			}
		}
		
		return $element;
	}
	
	/**
	 * Returns true if provided node is a block element.
	 * 
	 * @param	\DOMNode	$node		node
	 * @return	boolean		true for certain block elements
	 */
	protected function isBlockElement(\DOMNode $node) {
		switch ($node->nodeName) {
			case 'blockquote':
			case 'body':
			case 'code':
			case 'div':
			case 'p':
				return true;
				break;
			
			case 'woltlab-metacode':
				/** @var \DOMElement $node */
				if (in_array($node->getAttribute('data-name'), $this->blockElements)) {
					return true;
				}
				break;
		}
		
		return false;
	}
	
	/**
	 * Converts a bbcode marker pair into their plain bbcode representation. This method is used
	 * to convert markers inside source code elements.
	 * 
	 * @param	array		$pair		bbcode marker pair
	 */
	protected function convertToBBCode(array $pair) {
		/** @var \DOMElement $start */
		$start = $pair['open'];
		/** @var \DOMElement $end */
		$end = $pair['close'];
		
		$attributes = (isset($pair['attributes'])) ? $pair['attributes'] : '';
		$textNode = $start->ownerDocument->createTextNode(HtmlBBCodeParser::getInstance()->buildBBCodeTag($pair['name'], $attributes, true));
		DOMUtil::insertBefore($textNode, $start);
		DOMUtil::removeNode($start);
		
		$textNode = $end->ownerDocument->createTextNode('[/' . $pair['name'] . ']');
		DOMUtil::insertBefore($textNode, $end);
		DOMUtil::removeNode($end);
	}
}
