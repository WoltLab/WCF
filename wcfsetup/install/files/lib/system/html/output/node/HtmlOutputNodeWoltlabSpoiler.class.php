<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Processes spoilers.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeWoltlabSpoiler extends AbstractHtmlNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-spoiler';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'label' => ($element->hasAttribute('data-label')) ? $element->getAttribute('data-label') : ''
			]);
			
			$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		WCF::getTPL()->assign([
			'buttonLabel' => $data['label']
		]);
		return WCF::getTPL()->fetch('spoilerMetaCode');
	}
}
