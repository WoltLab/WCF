<?php
namespace wcf\system\html\output\node;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeWoltlabSize extends AbstractHtmlNode {
	protected $tagName = 'woltlab-size';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			// parse color
			if (preg_match('~^woltlab-size-(?P<size>[0-9]{1,2})$~', $element->getAttribute('class'), $matches)) {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
					'size' => $matches['size']
				]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
			}
		}
	}
	
	public function replaceTag(array $data) {
		return '<span style="font-size: ' . $data['size'] . 'px">' . self::PLACEHOLDER . '</span>';
	}
}
