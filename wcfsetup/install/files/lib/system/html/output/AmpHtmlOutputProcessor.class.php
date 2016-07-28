<?php
namespace wcf\system\html\output;
use wcf\util\DOMUtil;

/**
 * Processes stored HTML for final display and modifies/removes elements to
 * match the Google AMP specifications.
 * 
 * See https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output
 * @since       3.0
 */
class AmpHtmlOutputProcessor extends HtmlOutputProcessor {
	/**
	 * @inheritDoc
	 */
	public function process($html, $objectType, $objectID) {
		parent::process($html, $objectType, $objectID);
		
		$document = $this->getHtmlOutputNodeProcessor()->getDocument();
			
		// remove tags and discarding content
		$tags = [
			// general
			'base', 'frame', 'frameset', 'object', 'param', 'applet', 'embed',
			
			// forms
			'input', 'textarea', 'select', 'option',
			
			// special
			'style'
		];
		foreach ($tags as $tag) {
			$elements = $document->getElementsByTagName($tag);
			while ($elements->length) DOMUtil::removeNode($elements->item(0), true);
		}
		
		// remove tags but keep child nodes
		$tags = ['form'];
		foreach ($tags as $tag) {
			$elements = $document->getElementsByTagName($tag);
			while ($elements->length) DOMUtil::removeNode($elements->item(0), false);
		}
		
		// remove script tags unless the type is application/ld+json
		$elements = $this->filterElements(
			$document->getElementsByTagName('script'),
			function ($element) {
				/** @var \DOMElement $element */
				return ($element->getAttribute('type') === 'application/ld+json');
			}
		);
		foreach ($elements as $element) DOMUtil::removeNode($element);
		
		// replace tags
		$tags = ['img', 'video', 'audio', 'iframe'];
		foreach ($tags as $tag) {
			$elements = $document->getElementsByTagName($tag);
			while ($elements->length) {
				/** @var \DOMElement $element */
				$element = $elements->item(0);
				if ($tag === 'img') {
					$styles = $element->getAttribute('style');
					if (preg_match('~\bheight:\s*(\d+)px\b~', $styles, $matches)) $element->setAttribute('height', $matches[1]);
					if (preg_match('~\bwidth:\s*(\d+)px\b~', $styles, $matches)) $element->setAttribute('width', $matches[1]);
					
					if (!$element->getAttribute('height') || !$element->getAttribute('width')) {
						DOMUtil::removeNode($element);
						continue;
					}
					
					$element->removeAttribute('style');
				}
				
				$newElement = $element->ownerDocument->createElement('amp-' . $tag);
				
				// copy attributes
				for ($i = 0, $length = $element->attributes->length; $i < $length; $i++) {
					$attr = $element->attributes->item($i);
					
					$newElement->setAttribute($attr->localName, $attr->nodeValue);
				}
				
				$element->parentNode->insertBefore($newElement, $element);
				DOMUtil::removeNode($element);
			}
		}
	}
	
	/**
	 * Filters a list of elements using a callback. Return false from the callback
	 * to add the element to the list of bad elements.
	 * 
	 * @param       \DOMNodeList    $elements       list of possible elements
	 * @param       callable        $callback       validation callback, return false to flag element as bad
	 * @return      \DOMElement[]   list of bad elements
	 */
	protected function filterElements(\DOMNodeList $elements, callable $callback) {
		$badElements = [];
		
		foreach ($elements as $element) {
			if ($callback($element) === false) {
				$badElements[] = $element;
			}
		}
		
		return $badElements;
	}
}
