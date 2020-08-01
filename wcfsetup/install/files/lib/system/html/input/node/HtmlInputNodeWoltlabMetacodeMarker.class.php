<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Transforms bbcode markers into the custom HTML element `<woltlab-metacode>`. This process
 * outputs well-formed markup with proper element nesting.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Html\Input\Node
 * @since	3.0
 */
class HtmlInputNodeWoltlabMetacodeMarker extends AbstractHtmlInputNode {
	/**
	 * list of tag names that should be considered as block level elements
	 * @var string[]
	 */
	public static $customBlockElementTagNames = [];
	
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
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		// metacode-marker isn't present at time of validation
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		// collect pairs
		$pairs = $this->buildPairs($elements);
		
		// validate pairs and remove items that lack an opening/closing element
		$pairs = $this->validatePairs($pairs);
		
		// group pairs by tag name
		$groups = $this->groupPairsByName($pairs);
		
		$groups = $this->filterGroups($groups, $htmlNodeProcessor);
		if (empty($groups)) {
			return;
		}
		
		// convert source bbcode groups first to ensure no bbcodes inside
		// source blocks will be evaluated
		$groups = $this->convertSourceGroups($groups);
		
		$groups = $this->revertMarkerInsideCodeBlocks($groups, $htmlNodeProcessor);
		
		// convert pairs into HTML or metacode
		$this->convertGroups($groups);
	}
	
	/**
	 * Filters groups by reverting metacode markers for invalid bbcodes.
	 * 
	 * @param       array                           $groups                 grouped list of bbcode marker pairs
	 * @param       AbstractHtmlNodeProcessor       $htmlNodeProcessor      node processor instance
	 * @return      array                           filtered groups
	 */
	protected function filterGroups(array $groups, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @noinspection PhpUndefinedMethodInspection */
		$data = [
			'context' => $htmlNodeProcessor->getHtmlProcessor()->getContext(),
			'bbcodes' => array_keys($groups)
		];
		
		EventHandler::getInstance()->fireAction($this, 'filterGroups', $data);
		
		foreach ($groups as $name => $pairs) {
			if (!in_array($name, $data['bbcodes']) || !BBCodeHandler::getInstance()->isAvailableBBCode($name)) {
				foreach ($pairs as $pair) {
					$pair['attributes'] = $htmlNodeProcessor->parseAttributes($pair['attributes']);
					$this->convertToBBCode($name, $pair);
				}
				
				unset($groups[$name]);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Transforms bbcode markers inside source code elements into their plain bbcode representation.
	 *
	 * @param	array		                $groups		grouped list of bbcode marker pairs
	 * @param       AbstractHtmlNodeProcessor       $htmlNodeProcessor      node processor instance
	 * @return      array                           filtered groups without source bbcodes
	 */
	protected function revertMarkerInsideCodeBlocks(array $groups, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		foreach ($groups as $name => $pairs) {
			$needsReindex = false;
			for ($i = 0, $length = count($pairs); $i < $length; $i++) {
				$pair = $pairs[$i];
				if ($this->isInsideCode($pair['open']) || $this->isInsideCode($pair['close'])) {
					$pair['attributes'] = $htmlNodeProcessor->parseAttributes($pair['attributes']);
					$this->convertToBBCode($name, $pair);
					
					$needsReindex = true;
					unset($groups[$name][$i]);
					
					if (empty($groups[$name])) {
						$needsReindex = false;
						unset($groups[$name]);
					}
				}
			}
			
			if ($needsReindex) {
				$groups[$name] = array_values($groups[$name]);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Returns `true` if the given element is inside a code element.
	 * 
	 * @param	\DOMElement	$element
	 * @return	boolean
	 */
	protected function isInsideCode(\DOMElement $element) {
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
			$source = @base64_decode($element->getAttribute('data-source'));
			
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
				$pairs[$uuid]['openSource'] = $source;
				$pairs[$uuid]['useText'] = ($element->hasAttribute('data-use-text')) ? $element->getAttribute('data-use-text') : false;
			}
			else {
				$pairs[$uuid]['close'] = $element;
				$pairs[$uuid]['closeSource'] = $source;
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
				'closeSource' => $data['closeSource'],
				'open' => $data['open'],
				'openSource' => $data['openSource'],
				'useText' => $data['useText']
			];
		}
		
		return $groups;
	}
	
	/**
	 * Converts source bbcode groups.
	 *
	 * @param	array		$groups		grouped list of bbcode marker pairs
	 * @return      array           filtered groups without source bbcodes
	 */
	protected function convertSourceGroups(array $groups) {
		foreach ($this->sourceElements as $name) {
			if (!isset($groups[$name])) {
				continue;
			}
			
			for ($i = 0, $length = count($groups[$name]); $i < $length; $i++) {
				$data = $groups[$name][$i];
				if ($this->isInsideCode($data['open']) || $this->isInsideCode($data['close'])) {
					continue;
				}
				
				if (in_array($name, $this->blockElements)) {
					$this->convertBlockElement($name, $data['open'], $data['close'], $data['attributes']);
				}
				else {
					$this->convertInlineElement($name, $data['open'], $data['close'], $data['attributes']);
				}
				
				unset($groups[$name][$i]);
			}
			
			if (empty($groups[$name])) {
				unset($groups[$name]);
			}
			else {
				$groups[$name] = array_values($groups[$name]);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Converts bbcode marker pairs into block- or inline-elements.
	 * 
	 * @param	array		$groups		grouped list of bbcode marker pairs
	 */
	protected function convertGroups(array $groups) {
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
		$foundLi = false;
		do {
			$parent = $parent->parentNode;
			if (!$foundLi && $parent->nodeName === 'li') {
				// allow <li> if both the start and end have the same <li> as parent
				$parentEnd = $end;
				do {
					$parentEnd = $parentEnd->parentNode;
					if ($parentEnd === null) {
						break;
					}
					
					if ($parentEnd->nodeName === 'li') {
						if ($parent === $parentEnd) {
							// same ancestor, exit both loops
							break 2;
						}
						
						// mismatch
						break;
					}
				}
				while ($parentEnd);
				
				$foundLi = true;
			}
		}
		while ($parent->nodeName === 'p' || !$this->isBlockElement($parent));
		
		// block elements can sometimes contain a line break after the end tag
		// which needs to be removed to avoid it being split into a separate p
		if ($node = $end->nextSibling) {
			if ($node->nodeType === XML_TEXT_NODE && $node->textContent === "\n" || $node->textContent === "\r\n") {
				DOMUtil::removeNode($node);
			}
		}
		
		$element = DOMUtil::splitParentsUntil($start, $parent);
		if ($start !== $element) DOMUtil::insertBefore($start, $element);
		
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
			if ($element === null) $element = $commonAncestor;
			
			while ($element = $element->nextSibling) {
				if ($element->nodeType === XML_TEXT_NODE) {
					// ignore text nodes between tags
					continue;
				}
				
				if ($element !== $endAncestor) {
					if ($this->isBlockElement($element)) {
						if ($element->childNodes->length === 0) {
							$element->appendChild($element->ownerDocument->createTextNode(''));
						}
						
						$this->wrapContent($name, $attributes, $element->childNodes->item(0), null);
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
		
		$element = $startNode ? $startNode->ownerDocument->createElement('woltlab-metacode') : $endNode->ownerDocument->createElement('woltlab-metacode');
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
			case 'td':
			case 'woltlab-quote':
			case 'woltlab-spoiler':
				return true;
			
			case 'woltlab-metacode':
				/** @var \DOMElement $node */
				return in_array($node->getAttribute('data-name'), $this->blockElements);
				
			default:
				return in_array($node->nodeName, self::$customBlockElementTagNames);
		}
	}
	
	/**
	 * Converts a bbcode marker pair into their plain bbcode representation. This method is used
	 * to convert markers inside source code elements.
	 * 
	 * @param       string          $name           bbcode name
	 * @param	array		$pair		bbcode marker pair
	 */
	protected function convertToBBCode($name, array $pair) {
		/** @var \DOMElement $start */
		$start = $pair['open'];
		/** @var \DOMElement $end */
		$end = $pair['close'];
		
		$attributes = isset($pair['attributes']) ? $pair['attributes'] : [];
		$content = '';
		if (isset($pair['useText']) && $pair['useText'] !== false && isset($attributes[$pair['useText']])) {
			$content = array_splice($attributes, $pair['useText'])[0];
		}
		
		$textNode = $start->ownerDocument->createTextNode(($pair['openSource'] ?: HtmlBBCodeParser::getInstance()->buildBBCodeTag($name, $attributes, true)) . $content);
		DOMUtil::insertBefore($textNode, $start);
		DOMUtil::removeNode($start);
		
		$textNode = $end->ownerDocument->createTextNode($pair['closeSource'] ?: '[/' . $name . ']');
		DOMUtil::insertBefore($textNode, $end);
		DOMUtil::removeNode($end);
	}
}
