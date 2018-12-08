<?php
namespace wcf\system\html\output\node;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\RouteHandler;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes links.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeA extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'a';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$href = $element->getAttribute('href');
			if (ApplicationHandler::getInstance()->isInternalURL($href)) {
				$element->setAttribute('href', preg_replace('~^https?://~', RouteHandler::getProtocol(), $href));
			}
			else {
				self::markLinkAsExternal($element);
			}
			
			$value = StringUtil::trim($element->textContent);
			
			if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
				if (!empty($value) && $value === $href && mb_strlen($value) > 60) {
					while ($element->childNodes->length) {
						DOMUtil::removeNode($element->childNodes->item(0));
					}
					
					$element->appendChild(
						$element->ownerDocument->createTextNode(
							mb_substr($value, 0, 30) . StringUtil::HELLIP . mb_substr($value, -25)
						)
					);
				}
			}
			else if ($this->outputType === 'text/plain') {
				if (!empty($value) && $value !== $href) {
					$text = $value . ' [URL:' . $href . ']';
				}
				else {
					$text = $href;
				}

				$htmlNodeProcessor->replaceElementWithText($element, $text, false);
			}
		}
	}
	
	/**
	 * Marks an element as external.
	 * 
	 * @param       \DOMElement     $element
	 */
	public static function markLinkAsExternal(\DOMElement $element) {
		$element->setAttribute('class', 'externalURL');
		
		$rel = '';
		if (EXTERNAL_LINK_REL_NOFOLLOW) {
			$rel = 'nofollow';
		}
		
		if (EXTERNAL_LINK_TARGET_BLANK) {
			if (!empty($rel)) $rel .= ' ';
			
			$rel .= 'noopener noreferrer';
			
			$element->setAttribute('target', '_blank');
		}
		
		if (!empty($rel)) {
			$element->setAttribute('rel', $rel);
		}
	}
}
