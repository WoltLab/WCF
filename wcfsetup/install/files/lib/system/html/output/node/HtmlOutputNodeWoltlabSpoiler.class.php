<?php
namespace wcf\system\html\output\node;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	3.0
 */
class HtmlOutputNodeWoltlabSpoiler extends AbstractHtmlNode {
	protected $tagName = 'woltlab-spoiler';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'label' => ($element->hasAttribute('data-label')) ? $element->getAttribute('data-label') : ''
			]);
			
			$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
		}
	}
	
	public function replaceTag(array $data) {
		WCF::getTPL()->assign([
			'buttonLabel' => $data['label']
		]);
		return WCF::getTPL()->fetch('spoilerMetaCode');
	}
}
