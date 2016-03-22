<?php
namespace wcf\system\html\output;

/**
 * TOOD documentation
 * @since	2.2
 */
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
