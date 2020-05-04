<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Converts `<font>` elements into their CSS equivalent.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       5.2
 */
class HtmlInputNodeFont extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'font';
	
	/**
	 * @var string[]
	 */
	protected $sizeMapping = [
		'0' => '10px',
		'1' => '10px',
		'2' => '13px',
		'3' => '16px',
		'4' => '18px',
		'5' => '24px',
		'6' => '32px',
		'7' => '48px',
		
		'-1' => '13px',
		'-2' => '10px',
		
		'+1' => '18px',
		'+2' => '24px',
		'+3' => '32px',
		'+4' => '48px',
	];
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		$allowColor = BBCodeHandler::getInstance()->isAvailableBBCode('color');
		$allowFont = BBCodeHandler::getInstance()->isAvailableBBCode('font');
		$allowSize = BBCodeHandler::getInstance()->isAvailableBBCode('size');
		if ($allowColor && $allowFont && $allowSize) {
			return [];
		}
		
		$matches = [];
		
		/** @var \DOMElement $element */
		foreach ($htmlNodeProcessor->getDocument()->getElementsByTagName('font') as $element) {
			if (!$allowColor && $element->getAttribute('color')) {
				$matches[] = 'color';
			}
			else if (!$allowFont && $element->getAttribute('face')) {
				$matches[] = 'font';
			}
			else if (!$allowSize && $element->getAttribute('size')) {
				$matches[] = 'size';
			}
		}
		
		return array_unique($matches);
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
			
			if ($color = $element->getAttribute('color')) {
				$this->convertToSpan($element, 'color', $color);
			}
			if ($font = $element->getAttribute('face')) {
				$this->convertToSpan($element, 'font-family', $font);
			}
			if ($size = $element->getAttribute('size')) {
				if (isset($this->sizeMapping[$size])) {
					$this->convertToSpan($element, 'font-size', $this->sizeMapping[$size]);
				}
			}
			
			DOMUtil::removeNode($element, true);
		}
	}
	
	protected function convertToSpan(\DOMElement $element, $property, $value) {
		$span = $element->ownerDocument->createElement('span');
		$span->setAttribute('style',  "{$property}: {$value}");
		
		$element->parentNode->insertBefore($span, $element);
		$span->appendChild($element);
	}
}
