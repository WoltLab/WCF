<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Replaces the leading and trailing space with `&nbsp;` to prevent the browser from implicitly trimming
 * it on display.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       5.3
 */
class HtmlOutputNodeKbd extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'kbd';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$textContent = $element->textContent;
			
			if (mb_strlen($textContent) !== 0) {
				if ($textContent[0] === ' ') {
					$textContent = "\u{00A0}" . mb_substr($textContent, 1);
				}
				
				if (mb_substr($textContent, -1, 1) === ' ') {
					$textContent = mb_substr($textContent, 0, -1) . "\u{00A0}";
				}
				
				$element->textContent = $textContent;
			}
		}
	}
}
