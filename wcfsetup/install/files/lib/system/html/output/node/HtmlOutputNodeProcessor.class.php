<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\IHtmlNode;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

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
		
		if ($this->outputType !== 'text/html') {
			// convert `<p>...</p>` into `...<br><br>`
			$paragraphs = $this->getDocument()->getElementsByTagName('p');
			while ($paragraphs->length) {
				$paragraph = $paragraphs->item(0);
				
				for ($i = 0; $i < 2; $i++) {
					$br = $this->getDocument()->createElement('br');
					$paragraph->appendChild($br);
				}
				
				DOMUtil::removeNode($paragraph, true);
			}
			
			if ($this->outputType === 'text/plain') {
				// remove all `\n` first
				$nodes = [];
				/** @var \DOMText $node */
				foreach ($this->getXPath()->query('//text()') as $node) {
					if (strpos($node->textContent, "\n") !== false) {
						$nodes[] = $node;
					}
				}
				foreach ($nodes as $node) {
					$textNode = $this->getDocument()->createTextNode(preg_replace('~\r?\n~', '', $node->textContent));
					$node->parentNode->insertBefore($textNode, $node);
					$node->parentNode->removeChild($node);
				}
				
				// convert `<br>` into `\n`
				$brs = $this->getDocument()->getElementsByTagName('br');
				while ($brs->length) {
					$br = $brs->item(0);
					
					$newline = $this->getDocument()->createTextNode("\n");
					$br->parentNode->insertBefore($newline, $br);
					DOMUtil::removeNode($br);
				}
				
				// remove all other elements
				$elements = $this->getDocument()->getElementsByTagName('*');
				while ($elements->length) {
					DOMUtil::removeNode($elements->item(0), true);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		$html = parent::getHtml();
		
		if ($this->outputType === 'text/plain') {
			$html = StringUtil::trim($html);
		}
		
		return $html;
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
