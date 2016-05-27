<?php
namespace wcf\system\html\output;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputProcessor {
	/**
	 * @var	HtmlOutputNodeProcessor
	 */
	protected $htmlOutputNodeProcessor;
	
	public function process($html) {
		$this->getHtmlOutputNodeProcessor()->load($html);
		$this->getHtmlOutputNodeProcessor()->process();
		
		return $this->getHtmlOutputNodeProcessor()->getHtml();
	}
	
	protected function getHtmlOutputNodeProcessor() {
		if ($this->htmlOutputNodeProcessor === null) {
			$this->htmlOutputNodeProcessor = new HtmlOutputNodeProcessor();
		}
		
		return $this->htmlOutputNodeProcessor;
	}
}
