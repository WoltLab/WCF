<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	3.0
 */
class HtmlOutputNodeWoltlabColor extends AbstractHtmlNode {
	protected $tagName = 'woltlab-color';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			// parse color
			if (preg_match('~^woltlab-color-(?P<color>[A-F0-9]{6})$~', $element->getAttribute('class'), $matches)) {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
					'color' => $matches['color']
				]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
			}
		}
	}
	
	public function replaceTag(array $data) {
		return '<span style="color: #' . $data['color'] . '">' . self::PLACEHOLDER . '</span>';
	}
}
