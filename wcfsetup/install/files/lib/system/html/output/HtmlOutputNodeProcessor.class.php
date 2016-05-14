<?php
namespace wcf\system\html\output;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\html\output\node\HtmlOutputNodeBlockquote;
use wcf\system\html\output\node\HtmlOutputNodeWoltlabMention;

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
