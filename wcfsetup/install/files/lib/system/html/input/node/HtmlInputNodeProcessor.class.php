<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\HtmlNodeProcessor;

class HtmlInputNodeProcessor extends HtmlNodeProcessor {
	public function load($html) {
		parent::load($html);
		
		$this->nodeData = [];
	}
	
	public function process() {
		$woltlabMention = new HtmlInputNodeWoltlabMention();
		$woltlabMention->process($this);
	}
}
