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
	 * desired output type
	 * @var string
	 */
	protected $outputType = 'text/html';
	
	/**
	 * @inheritDoc
	 */
	public function process($html, $objectType, $objectID = 0) {
		if ($objectID === 0) {
			throw new \UnexpectedValueException('Object id cannot be 0 for output processing.');
		}
		
		$this->setContext($objectType, $objectID);
		
		$this->getHtmlOutputNodeProcessor()->setOutputType($this->outputType);
		$this->getHtmlOutputNodeProcessor()->load($this, $html);
		$this->getHtmlOutputNodeProcessor()->process();
	}
	
	/**
	 * Sets the desired output type.
	 * 
	 * @param       string          $outputType     desired output type
	 * @throws      \InvalidArgumentException
	 */
	public function setOutputType($outputType) {
		if (!in_array($outputType, ['text/html', 'text/simplified-html', 'text/plain'])) {
			throw new \InvalidArgumentException("Expected 'text/html', 'text/simplified-html' or 'text/plain', but received '" . $outputType . "'");
		}
		
		$this->outputType = $outputType;
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
