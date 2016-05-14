<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\HtmlNodeProcessor;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeProcessor extends HtmlNodeProcessor {
	public function process() {
		// TODO: this should be dynamic to some extent
		$this->invokeHtmlNode(new HtmlOutputNodeBlockquote());
		$this->invokeHtmlNode(new HtmlOutputNodeWoltlabMention());
	}
}
