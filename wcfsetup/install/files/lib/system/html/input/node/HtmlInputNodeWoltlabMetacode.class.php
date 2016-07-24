<?php
namespace wcf\system\html\input\node;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\metacode\converter\IMetacodeConverter;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes `<woltlab-metacode>` and converts them if appropriate.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeWoltlabMetacode extends AbstractHtmlInputNode {
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
	
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-metacode';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		$bbcodes = [];
		/** @var \DOMElement $element */
		foreach ($htmlNodeProcessor->getDocument()->getElementsByTagName($this->tagName) as $element) {
			$bbcodes[] = $element->getAttribute('data-name');
		}
		
		$disallowedBBCodes = [];
		foreach ($bbcodes as $bbcode) {
			if (BBCodeHandler::getInstance()->isAvailableBBCode($bbcode)) {
				continue;
			}
			
			$disallowedBBCodes[] = $bbcode;
		}
		
		return $disallowedBBCodes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var IMetacodeConverter[] $converters */
		$converters = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$name = $element->getAttribute('data-name');
			if ($name === 'abstract') {
				continue;
			}
			
			// handle simple mapping types
			if (isset($this->simpleMapping[$name])) {
				$newElement = $element->ownerDocument->createElement($this->simpleMapping[$name]);
				DOMUtil::replaceElement($element, $newElement);
				
				continue;
			}
			
			$attributes = $htmlNodeProcessor->parseAttributes($element->getAttribute('data-attributes'));
			
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
				// check if the bbcode's content should be used as first attribute and it
				// matches the elements content
				$bbcode = BBCodeCache::getInstance()->getBBCodeByTag($name);
				if ($bbcode !== null) {
					$bbcodeAttributes = $bbcode->getAttributes();
					$attr = (isset($bbcodeAttributes[0])) ? $bbcodeAttributes[0] : null;
					
					if ($attr !== null && $attr->useText && !empty($attributes[0]) && StringUtil::trim($attributes[0]) == StringUtil::trim($element->textContent)) {
						// discard content as it is already present in the first attribute
						$element->textContent = '';
					}
				}
				
				// no available converter, metacode will be handled during output generation
				continue;
			}
			
			/** @var IMetacodeConverter $converter */
			if ($converter->validateAttributes($attributes)) {
				$fragment = DOMUtil::childNodesToFragment($element);
				if (!$fragment->hasChildNodes()) $fragment->appendChild($fragment->ownerDocument->createTextNode(''));
				
				$newElement = $converter->convert($fragment, $attributes);
				if (!($newElement instanceof \DOMElement)) {
					throw new \UnexpectedValueException("Expected a valid DOMElement as return value.");
				}
				
				DOMUtil::replaceElement($element, $newElement);
			}
			else {
				// attributes are invalid, remove element from DOM
				DOMUtil::removeNode($element, true);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		return $data['parsedTag'];
	}
}
