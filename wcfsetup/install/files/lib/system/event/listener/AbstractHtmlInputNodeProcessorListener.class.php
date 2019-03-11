<?php
namespace wcf\system\event\listener;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ITitledObject;
use wcf\system\exception\ImplementationException;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\Regex;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * Provides useful methods for event listeners replacing simple text links in texts
 * with links with the name of the linked object or with bbcodes.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 * @since	3.0
 */
abstract class AbstractHtmlInputNodeProcessorListener implements IParameterizedEventListener {
	/**
	 * Returns the ids of the objects linked in the text processed by the given
	 * processor matching the given regular expression.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @return	integer[]
	 */
	protected function getObjectIDs(HtmlInputNodeProcessor $processor, Regex $regex) {
		$objectIDs = [];
		foreach ($processor->getDocument()->getElementsByTagName('a') as $element) {
			/** @var \DOMElement $element */
			if ($element->getAttribute('href') === $element->textContent) {
				if ($regex->match($element->getAttribute('href'), true)) {
					$objectIDs[] = $regex->getMatches()[2][0];
				}
			}
		}
		
		return array_unique($objectIDs);
	}
	
	/**
	 * Returns a `Regex` object for matching links based on the given string
	 * followed by an object id.
	 * 
	 * @param	string		$link
	 * @param       string          $defaultAnchor
	 * @return	Regex
	 */
	protected function getRegexFromLink($link, $defaultAnchor = '') {
		return new Regex('^(' . preg_replace('~^https?~', 'https?', preg_quote($link)) . '(\d+)-[^#]*?)' . ($defaultAnchor ? '(?:#' . $defaultAnchor . ')?' : '') . '$');
	}
	
	/**
	 * Replaces relevant object links with bbcodes.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @param	ITitledObject[]			$objects
	 * @param	string				$bbcodeName
	 */
	protected function replaceLinksWithBBCode(HtmlInputNodeProcessor $processor, Regex $regex, array $objects, $bbcodeName) {
		$elements = [];
		foreach ($processor->getDocument()->getElementsByTagName('a') as $element) {
			/** @var \DOMElement $element */
			if ($element->getAttribute('href') === $element->textContent) {
				if ($regex->match($element->getAttribute('href'), true)) {
					$objectID = $regex->getMatches()[2][0];
					
					if (isset($objects[$objectID])) {
						$elements[] = [
							'element' => $element,
							'objectID' => $objectID
						];
					}
				}
			}
		}
		
		foreach ($elements as $elementData) {
			$metacodeElement = $processor->getDocument()->createElement('woltlab-metacode');
			$metacodeElement->setAttribute('data-name', $bbcodeName);
			$metacodeElement->setAttribute('data-attributes', base64_encode(JSON::encode([$elementData['objectID']])));
			
			DOMUtil::replaceElement($elementData['element'], $metacodeElement, false);
		}
	}
	
	/**
	 * Replaces the text content of relevant object links with the titles of
	 * the objects.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @param	ITitledObject[]			$objects
	 * @throws	ImplementationException
	 */
	protected function setObjectTitles(HtmlInputNodeProcessor $processor, Regex $regex, array $objects) {
		foreach ($processor->getDocument()->getElementsByTagName('a') as $element) {
			/** @var \DOMElement $element */
			if ($element->getAttribute('href') === $element->textContent) {
				if ($regex->match($element->getAttribute('href'), true)) {
					$objectID = $regex->getMatches()[2][0];
					
					if (isset($objects[$objectID])) {
						$object = $objects[$objectID];
						if (!($object instanceof ITitledObject) && !($object instanceof DatabaseObjectDecorator) && !($object->getDecoratedObject() instanceof ITitledObject)) {
							throw new ImplementationException(get_class($object), ITitledObject::class);
						}
						
						$element->nodeValue = '';
						$element->appendChild($element->ownerDocument->createTextNode($object->getTitle()));
					}
				}
			}
		}
	}
}
