<?php
namespace wcf\system\html\input\node;
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
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			if (!preg_match('~\bwoltlabAttachment\b~', $class)) {
				continue;
			}
			
			$attachmentID = intval($element->getAttribute('data-attachment-id'));
			if (!$attachmentID) {
				continue;
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
	}
}
