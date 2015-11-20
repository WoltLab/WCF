<?php
namespace wcf\system\html\input;

use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\WCF;

class HtmlInputProcessor {
	/**
	 * @var IHtmlInputFilter
	 */
	protected $htmlInputFilter;
	
	public function process($html) {
		// filter HTML
		return $this->getHtmlInputFilter()->apply($html);
		
	}
	
	public function setHtmlInputFilter(IHtmlInputFilter $htmlInputFilter) {
		$this->htmlInputFilter = $htmlInputFilter;
	}
	
	/**
	 * @return IHtmlInputFilter
	 */
	public function getHtmlInputFilter() {
		if ($this->htmlInputFilter === null) {
			$this->htmlInputFilter = WCF::getDIContainer()->make(MessageHtmlInputFilter::class);
		}
		
		return $this->htmlInputFilter;
	}
}
