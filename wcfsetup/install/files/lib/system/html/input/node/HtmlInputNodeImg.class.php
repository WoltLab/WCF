<?php
namespace wcf\system\html\input\node;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;

/**
 * Processes `<img>` to handle embedded attachments.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeImg extends AbstractHtmlInputNode {
	/**
	 * number of found smilies
	 * @var integer
	 */
	protected $smiliesFound = 0;
	
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'img';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $nodeProcessor) {
		if (BBCodeHandler::getInstance()->isAvailableBBCode('img')) {
			return [];
		}
		
		$foundImage = false;
		
		// check if there are only attachments, media or smilies
		/** @var \DOMElement $element */
		foreach ($nodeProcessor->getDocument()->getElementsByTagName('img') as $element) {
			$class = $element->getAttribute('class');
			if (!preg_match('~\b(?:woltlabAttachment|woltlabSuiteMedia|smiley)\b~', $class)) {
				$foundImage = true;
				break;
			}
		}
		
		if (!$foundImage) {
			return [];
		}
		
		return ['img'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		$this->smiliesFound = 0;
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			if (preg_match('~\bwoltlabAttachment\b~', $class)) {
				$this->handleAttachment($element, $class);
			}
			else if (preg_match('~\bwoltlabSuiteMedia\b~', $class)) {
				$this->handleMedium($element, $class);
			}
			else if (preg_match('~\bsmiley\b~', $class)) {
				$this->handleSmiley($element);
			}
		}
	}
	
	/**
	 * Returns the number of smilies found within the message.
	 * 
	 * @return      integer
	 */
	public function getSmileyCount() {
		return $this->smiliesFound;
	}
	
	/**
	 * Replaces image element with attachment metacode element. 
	 * 
	 * @param	\DOMElement	$element
	 * @param	string		$class
	 */
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
	
	/**
	 * Replaces image element with media metacode element.
	 * 
	 * @param	\DOMElement	$element
	 * @param	string		$class
	 */
	protected function handleMedium(\DOMElement $element, $class) {
		$mediumID = intval($element->getAttribute('data-media-id'));
		if (!$mediumID) {
			return;
		}
		
		$float = 'none';
		$thumbnail = 'original';
		
		if (preg_match('~thumbnail=(?P<thumbnail>tiny|small|large|medium)\b~', $element->getAttribute('src'), $matches)) {
			$thumbnail = $matches['thumbnail'];
		}
		
		if (preg_match('~\bmessageFloatObject(?P<float>Left|Right)\b~', $class, $matches)) {
			$float = ($matches['float'] === 'Left') ? 'left' : 'right';
		}
		
		$attributes = [
			$mediumID,
			$thumbnail,
			$float
		];
		
		$newElement = $element->ownerDocument->createElement('woltlab-metacode');
		$newElement->setAttribute('data-name', 'wsm');
		$newElement->setAttribute('data-attributes', base64_encode(JSON::encode($attributes)));
		DOMUtil::replaceElement($element, $newElement, false);
		
		// The media bbcode is a block element that may not be placed inside inline elements.
		$parent = $newElement;
		$blockLevelParent = null;
		$blockElements = HtmlBBCodeParser::getInstance()->getBlockBBCodes();
		while ($parent = $parent->parentNode) {
			switch ($parent->nodeName) {
				case 'blockquote':
				case 'body':
				case 'code':
				case 'div':
				case 'p':
				case 'td':
				case 'woltlab-quote':
				case 'woltlab-spoiler':
					$blockLevelParent = $parent;
					break 2;
				
				case 'woltlab-metacode':
					if (in_array($parent->getAttribute('data-name'), $blockElements)) {
						$blockLevelParent = $parent;
						break 2;
					}
					break;
			}
		}
		
		if ($blockLevelParent !== null) {
			$element = DOMUtil::splitParentsUntil($newElement, $parent);
			if ($element !== $newElement) {
				DOMUtil::insertBefore($newElement, $element);
			}
		}
	}
	
	/**
	 * Replaces image element with smiley metacode element.
	 * 
	 * @param	\DOMElement	$element
	 */
	protected function handleSmiley(\DOMElement $element) {
		$code = $element->getAttribute('alt');
		
		/** @var Smiley $smiley */
		$smiley = SmileyCache::getInstance()->getSmileyByCode($code);
		if ($smiley === null || $this->smiliesFound === 50) {
			$element->parentNode->insertBefore($element->ownerDocument->createTextNode($code), $element);
			$element->parentNode->removeChild($element);
		}
		else {
			// enforce database values for src, srcset and style
			$element->setAttribute('src', $smiley->getURL());
			
			if ($smiley->getHeight()) $element->setAttribute('height', (string)$smiley->getHeight());
			else $element->removeAttribute('height');
			
			if ($smiley->smileyPath2x) $element->setAttribute('srcset', $smiley->getURL2x() . ' 2x');
			else $element->removeAttribute('srcset');
			
			$this->smiliesFound++;
		}
	}
}
