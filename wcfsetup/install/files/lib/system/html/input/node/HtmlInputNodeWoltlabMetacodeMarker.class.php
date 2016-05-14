<?php
namespace wcf\system\html\input\node;
use wcf\system\exception\SystemException;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeWoltlabMetacodeMarker extends AbstractHtmlNode {
	public $blockElements = ['code', 'quote'];
	public $inlineElements = ['b', 'color', 'i', 'tt', 'u'];
	public $sourceElements = ['code', 'tt'];
	
	protected $tagName = 'woltlab-metacode-marker';
	
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
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
	
	public function replaceTag(array $data) {
		return $data['parsedTag'];
	}
	
	/**
	 * @param array $pairs
	 * @return array
	 */
	protected function revertMarkerInsideCodeBlocks(array $pairs) {
		$isInsideCode = function(\DOMElement $element) {
			$parent = $element;
			while ($parent = $parent->parentNode) {
				$nodeName = $parent->nodeName;
				
				if ($nodeName === 'code' || $nodeName === 'kbd') {
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
	 * @param string $name
	 * @param \DOMElement $start
	 * @param \DOMElement $end
	 * @param string $attributes
	 */
	protected function convertBlockElement($name, $start, $end, $attributes) {
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
	 * @param string $name
	 * @param \DOMElement $start
	 * @param \DOMElement $end
	 * @param string $attributes
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
	 * @param string $name
	 * @param string $attributes
	 * @param \DOMElement|null $startNode
	 * @param \DOMElement|null $endNode
	 * @return      \DOMElement
	 */
	protected function wrapContent($name, $attributes, $startNode, $endNode) {
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
	 * @param       \DOMNode        $node           node
	 * @return      boolean         true for certain block elements
	 */
	protected function isBlockElement(\DOMNode $node) {
		switch ($node->nodeName) {
			case 'blockquote':
			case 'code':
			case 'div':
			case 'p':
				return true;
		}
		
		return false;
	}
	
	protected function convertToBBCode(array $pair) {
		/** @var \DOMElement $start */
		$start = $pair['open'];
		/** @var \DOMElement $end */
		$end = $pair['close'];
		
		$attributes = '';
		if (!empty($pair['attributes'])) {
			$pair['attributes'] = base64_decode($pair['attributes'], true);
			if ($pair['attributes'] !== false) {
				try {
					$pair['attributes'] = JSON::decode($pair['attributes']);
				}
				catch (SystemException $e) {
					$pair['attributes'] = [];
				}
				
				if (!empty($pair['attributes'])) {
					foreach ($pair['attributes'] as &$attribute) {
						$attribute = "'" . addcslashes($attribute, "'") . "'";
					}
					unset($attribute);
					
					$attributes = '=' . implode(",", $attributes);
				}
			}
		}
		
		$textNode = $start->ownerDocument->createTextNode('[' . $pair['name'] . $attributes . ']');
		DOMUtil::insertBefore($textNode, $start);
		DOMUtil::removeNode($start);
		
		$textNode = $end->ownerDocument->createTextNode('[/' . $pair['name'] . ']');
		DOMUtil::insertBefore($textNode, $end);
		DOMUtil::removeNode($end);
	}
}
