<?php
namespace wcf\system\html\input\node;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * Processes `<img>` to handle embedded attachments.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeImg extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'img';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $nodeProcessor) {
		return BBCodeHandler::getInstance()->isAvailableBBCode('img') ? [] : ['img'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			if (preg_match('~\bwoltlabAttachment\b~', $class)) {
				$this->handleAttachment($element, $class);
			}
			else if (preg_match('~\bsmiley\b~', $class)) {
				$this->handleSmiley($element);
			}
		}
	}
	
	protected function handleAttachment(\DOMElement $element, $class) {
		$attachmentID = intval($element->getAttribute('data-attachment-id'));
		if (!$attachmentID) {
			return;
		}
		
		$float = 'none';
		$thumbnail = false;
		
		if (strpos($element->getAttribute('src'), 'thumbnail=1') !== false) {
			$thumbnail = true;
		}
		
		if (preg_match('~\bmessageFloatObject(?P<float>Left|Right)\b~', $class, $matches)) {
			$float = ($matches['float'] === 'Left') ? 'left' : 'right';
		}
		
		$attributes = [
			$attachmentID,
			$float,
			$thumbnail
		];
		
		$newElement = $element->ownerDocument->createElement('woltlab-metacode');
		$newElement->setAttribute('data-name', 'attach');
		$newElement->setAttribute('data-attributes', base64_encode(JSON::encode($attributes)));
		DOMUtil::replaceElement($element, $newElement, false);
	}
	
	protected function handleSmiley(\DOMElement $element) {
		$code = $element->getAttribute('alt');
		
		/** @var Smiley $smiley */
		$smiley = SmileyCache::getInstance()->getSmileyByCode($code);
		if ($smiley === null) {
			$element->parentNode->insertBefore($element->ownerDocument->createTextNode($code), $element);
			$element->parentNode->removeChild($element);
		}
		else {
			// enforce database values for src, srcset and style
			$element->setAttribute('src', $smiley->getURL());
			
			if ($smiley->getHeight()) $element->setAttribute('height', $smiley->getHeight());
			else $element->removeAttribute('height');
			
			if ($smiley->smileyPath2x) $element->setAttribute('srcset', $smiley->getURL2x() . ' 2x');
			else $element->removeAttribute('srcset');
		}
	}
}
