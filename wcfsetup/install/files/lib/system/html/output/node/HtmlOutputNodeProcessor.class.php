<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\IHtmlNode;

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
	protected $nodeInterface = IHtmlOutputNode::class;
	
	/**
	 * desired output type
	 * @var string
	 */
	protected $outputType = 'text/html';
	
	/**
	 * Sets the desired output type.
	 * 
	 * @param       string          $outputType     desired output type
	 */
	public function setOutputType($outputType) {
		$this->outputType = $outputType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function process() {
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabMetacode());
		
		// dynamic node handlers
		$this->invokeNodeHandlers('wcf\system\html\output\node\HtmlOutputNode', ['woltlab-metacode']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function invokeHtmlNode(IHtmlNode $htmlNode) {
		/** @var IHtmlOutputNode $htmlNode */
		$htmlNode->setOutputType($this->outputType);
		
		parent::invokeHtmlNode($htmlNode);
	}
}
