<?php
namespace wcf\system\html\input;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\html\input\node\IHtmlInputNodeProcessor;
use wcf\system\html\node\IHtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	3.0
 */
class HtmlInputProcessor {
	protected $embeddedContent = [];
	
	/**
	 * @var	IHtmlInputFilter
	 */
	protected $htmlInputFilter;
	
	/**
	 * @var	IHtmlInputNodeProcessor
	 */
	protected $htmlInputNodeProcessor;
	
	public function process($html) {
		// enforce consistent newlines
		$html = StringUtil::unifyNewlines($html);
		
		// transform bbcodes into metacode markers
		$html = HtmlBBCodeParser::getInstance()->parse($html);
		
		// filter HTML
		$html = $this->getHtmlInputFilter()->apply($html);
		
		// pre-parse HTML
		$this->getHtmlInputNodeProcessor()->load($html);
		$this->getHtmlInputNodeProcessor()->process();
		$this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
	}
	
	public function validate() {
		// TODO
	}
	
	public function getHtml() {
		return $this->getHtmlInputNodeProcessor()->getHtml();
	}
	
	/**
	 * @return	IHtmlInputFilter
	 */
	public function getHtmlInputFilter() {
		if ($this->htmlInputFilter === null) {
			$this->htmlInputFilter = new MessageHtmlInputFilter();
		}
		
		return $this->htmlInputFilter;
	}
	
	public function setHtmlInputFilter(IHtmlInputFilter $htmlInputFilter) {
		$this->htmlInputFilter = $htmlInputFilter;
	}
	
	/**
	 * @return IHtmlInputNodeProcessor
	 */
	public function getHtmlInputNodeProcessor() {
		if ($this->htmlInputNodeProcessor === null) {
			$this->htmlInputNodeProcessor = new HtmlInputNodeProcessor();
		}
		
		return $this->htmlInputNodeProcessor;
	}
	
	public function setHtmlInputNodeProcessor(IHtmlNodeProcessor $htmlInputNodeProcessor) {
		$this->htmlInputNodeProcessor = $htmlInputNodeProcessor;
	}
	
	public function getEmbeddedContent() {
		return $this->embeddedContent;
	}
}
