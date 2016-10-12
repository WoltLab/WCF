<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * Processes text color.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeWoltlabColor extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-color';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
			/** @var \DOMElement $element */
			foreach ($elements as $element) {
				// parse color
				if (preg_match('~^woltlab-color-(?P<color>[A-F0-9]{6})$~', $element->getAttribute('class'), $matches)) {
					$nodeIdentifier = StringUtil::getRandomID();
					$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['color' => $matches['color']]);
					
					$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		return '<span style="color: #' . $data['color'] . '">' . self::PLACEHOLDER . '</span>';
	}
}
