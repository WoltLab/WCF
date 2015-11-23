<?php
namespace wcf\system\html\input\node;

use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\WCF;

class HtmlInputNodeProcessor extends HtmlNodeProcessor {
	public function load($html) {
		parent::load($html);
		
		$this->nodeData = [];
	}
	
	public function process() {
		$woltlabMention = WCF::getDIContainer()->get(HtmlInputNodeWoltlabMention::class);
		$woltlabMention->process($this);
	}
}
