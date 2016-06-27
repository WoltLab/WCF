<?php
namespace wcf\system\html\input\node;
use wcf\system\html\metacode\converter\IMetacodeConverter;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

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
				// no available converter, metacode will be handled during output generation
				continue;
			}
			
			/** @var IMetacodeConverter $converter */
			if ($converter->validateAttributes($attributes)) {
				$newElement = $converter->convert(DOMUtil::childNodesToFragment($element), $attributes);
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
