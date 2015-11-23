<?php
namespace wcf\system\html\output;

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
		$this->htmlOutputNodeProcessor->process();
		
		return $this->htmlOutputNodeProcessor->getHtml();
	}
}
