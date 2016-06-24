<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Processes a HTML string and renders the final output for display.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeProcessor extends AbstractHtmlNodeProcessor {
	/**
	 * @inheritDoc
	 */
	public function process() {
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabMetacode());
		
		// dynamic node handlers
		$this->invokeNodeHandlers('wcf\system\html\output\node\HtmlOutputNode', ['woltlab-metacode']);
	}
}
