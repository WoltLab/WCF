<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\HtmlNodeProcessor;

/**
 * TOOD documentation
 * @since	3.0
 */
class HtmlOutputNodeProcessor extends HtmlNodeProcessor {
	public function process() {
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabMetacode());
		
		// TODO: this should be dynamic to some extent
		$this->invokeHtmlNode(new HtmlOutputNodeBlockquote());
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabMention());
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabColor());
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabSize());
	}
}
