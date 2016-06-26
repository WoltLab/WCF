<?php
namespace wcf\system\html\output;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;
use wcf\system\html\AbstractHtmlProcessor;

/**
 * Processes stored HTML for final display.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output
 * @since       3.0
 */
class HtmlOutputProcessor extends AbstractHtmlProcessor {
	/**
	 * output node processor instance
	 * @var	HtmlOutputNodeProcessor
	 */
	protected $htmlOutputNodeProcessor;
	
	/**
	 * @inheritDoc
	 */
	public function process($html, $objectType, $objectID) {
		$this->setContext($objectType, $objectID);
		
		$this->getHtmlOutputNodeProcessor()->load($this, $html);
		$this->getHtmlOutputNodeProcessor()->process();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return $this->getHtmlOutputNodeProcessor()->getHtml();
	}
	
	/**
	 * Returns the output node processor instance.
	 * 
	 * @return      HtmlOutputNodeProcessor         output node processor instance
	 */
	protected function getHtmlOutputNodeProcessor() {
		if ($this->htmlOutputNodeProcessor === null) {
			$this->htmlOutputNodeProcessor = new HtmlOutputNodeProcessor();
		}
		
		return $this->htmlOutputNodeProcessor;
	}
}
