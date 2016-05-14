<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\exception\SystemException;
use wcf\system\html\metacode\converter\IMetacodeConverter;
use wcf\system\html\metacode\converter\SimpleMetacodeConverter;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeWoltlabMetacode extends AbstractHtmlNode {
	/**
	 * static mapping of attribute-less metacodes that map to
	 * an exact HTML tag without the need of further processing
	 *
	 * @var string[]
	 */
	public $simpleMapping = [
		'b' => 'strong',
		'i' => 'em',
		'tt' => 'kbd',
		'u' => 'u'
	];
	
	protected $tagName = 'woltlab-metacode';
	
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var IMetacodeConverter[] $converters */
		$converters = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$name = $element->getAttribute('data-name');
			if ($name === 'abstract') {
				continue;
			}
			
			// handle simple mapping types
			if (isset($name, $this->simpleMapping)) {
				$newElement = $element->ownerDocument->createElement($this->simpleMapping[$name]);
				DOMUtil::replaceElement($element, $newElement);
				
				continue;
			}
			
			$attributes = $element->getAttribute('data-attributes');
			if (!empty($attributes)) $attributes = @json_decode(base64_decode($attributes), true);
			if (!is_array($attributes)) $attributes = [];
			
			// check for converters
			$converter = (isset($converters[$name])) ? $converters[$name] : null;
			if ($converter === null) {
				$className = 'wcf\\system\\html\\metacode\\converter\\' . $name . 'MetacodeConverter';
				if (class_exists($className)) {
					$converter = new $className();
					
					$converters[$name] = $converter;
				}
			}
			
			if ($converter === null) {
				// no available converter, metacode will be handled during output generation
				continue;
			}
			
			/** @var IMetacodeConverter $converter */
			if ($converter->validateAttributes($attributes)) {
				$newElement = $converter->convert(DOMUtil::childNodesToFragment($element), $attributes);
				if (!($newElement instanceof \DOMElement)) {
					throw new SystemException("Expected a valid DOMElement as return value.");
				}
				
				DOMUtil::replaceElement($element, $newElement);
			}
			else {
				// attributes are invalid, remove element from DOM
				DOMUtil::removeNode($element, true);
			}
		}
	}
	
	public function replaceTag(array $data) {
		return $data['parsedTag'];
	}
	
	protected function getPlaceholderElement() {
		return new \DOMElement('woltlab-placeholder');
	}
}
