<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Processes `<table>` and removes invalid tables. This action can
 * be done safely, because completely messed up tables have already
 * been crippled by HTMLPurifier.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeTable extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'table';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$trs = [];
			/** @var \DOMElement $tr */
			foreach ($element->getElementsByTagName('tr') as $tr) {
				$trs[] = $tr;
			}
			
			// check if each `<tr>` has at least one `<td>`
			foreach ($trs as $tr) {
				$childTagName = 'td';
				if ($tr->parentNode->nodeName === 'thead') {
					$childTagName = 'th';
				}
				
				if ($tr->getElementsByTagName($childTagName)->length === 0) {
					DOMUtil::removeNode($tr);
				}
			}
			
			if ($element->getElementsByTagName('tr')->length === 0) {
				// garbage table, remove
				DOMUtil::removeNode($element);
			}
		}
	}
}
