<?php
namespace wcf\system\html\output\node;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * Processes bbcodes represented by `<woltlab-metacode>`.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeWoltlabMetacode extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-metacode';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
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
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		HtmlBBCodeParser::getInstance()->setOutputType($this->outputType);
		
		return HtmlBBCodeParser::getInstance()->getHtmlOutput($data['name'], $data['attributes']);
	}
}
