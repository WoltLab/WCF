<?php
namespace wcf\system\html\input;

use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\html\input\node\HtmlInputNodeProcessor;

class HtmlInputProcessor {
	/**
	 * @var IHtmlInputFilter
	 */
	protected $htmlInputFilter;
	
	/**
	 * @var HtmlInputNodeProcessor
	 */
	protected $htmlInputNodeProcessor;
	
	public function process($html) {
		// filter HTML
		$html = $this->getHtmlInputFilter()->apply($html);
		
		// pre-parse HTML
		$this->getHtmlInputNodeProcessor()->load($html);
		$this->getHtmlInputNodeProcessor()->process();
		
		return $this->getHtmlInputNodeProcessor()->getHtml();
	}
	
	public function setHtmlInputFilter(IHtmlInputFilter $htmlInputFilter) {
		$this->htmlInputFilter = $htmlInputFilter;
	}
	
	/**
	 * @return IHtmlInputFilter|MessageHtmlInputFilter
	 */
	public function getHtmlInputFilter() {
		if ($this->htmlInputFilter === null) {
			$this->htmlInputFilter = new MessageHtmlInputFilter();
		}
		
		return $this->htmlInputFilter;
	}
	
	public function setHtmlInputNodeProcessor(HtmlInputNodeProcessor $htmlInputNodeProcessor) {
		$this->htmlInputNodeProcessor = $htmlInputNodeProcessor;
	}
	
	/**
	 * @return HtmlInputNodeProcessor
	 */
	public function getHtmlInputNodeProcessor() {
		if ($this->htmlInputNodeProcessor === null) {
			$this->htmlInputNodeProcessor = new HtmlInputNodeProcessor();
		}
		
		return $this->htmlInputNodeProcessor;
	}
}
