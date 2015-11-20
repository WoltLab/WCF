<?php
namespace wcf\system\html\output;

use wcf\system\WCF;

class HtmlOutputProcessor {
	/**
	 * @var HtmlOutputNodeProcessor
	 */
	protected $htmlOutputNodeProcessor;
	
	public function __construct(HtmlOutputNodeProcessor $htmlOutputNodeProcessor) {
		$this->htmlOutputNodeProcessor = $htmlOutputNodeProcessor;
	}
	
	public function process($html) {
		$this->htmlOutputNodeProcessor->load($html);
		$html = $this->htmlOutputNodeProcessor->process();
		
		return $html;
	}
}
