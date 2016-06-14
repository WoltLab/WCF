<?php
namespace wcf\system\html\output\node;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	3.0
 */
class HtmlOutputNodeWoltlabMetacode extends AbstractHtmlNode {
	protected $tagName = 'woltlab-metacode';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$name = $element->getAttribute('data-name');
			$attributes = $element->getAttribute('data-attributes');
			
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'name' => $name,
				'attributes' => $htmlNodeProcessor->parseAttributes($attributes)
			]);
			
			$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
		}
	}
	
	public function replaceTag(array $data) {
		return HtmlBBCodeParser::getInstance()->getHtmlOutput($data['name'], $data['attributes']);
	}
}
