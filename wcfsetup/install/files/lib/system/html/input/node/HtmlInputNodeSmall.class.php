<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Converts `<small>` elements into their CSS equivalent.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       5.3
 */
class HtmlInputNodeSmall extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'small';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		if (BBCodeHandler::getInstance()->isAvailableBBCode('size')) {
			return [];
		}
		
		return ['size'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			if (DOMUtil::isRemoved($element) || !$element->parentNode) {
				continue;
			}
			
			$span = $element->ownerDocument->createElement('span');
			$span->setAttribute('style', "font-size: 10pt");
			
			$element->parentNode->insertBefore($span, $element);
			$span->appendChild($element);
			
			DOMUtil::removeNode($element, true);
		}
	}
}
