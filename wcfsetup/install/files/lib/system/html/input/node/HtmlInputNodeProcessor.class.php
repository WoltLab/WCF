<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\HtmlNodeProcessor;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlInputNodeProcessor extends HtmlNodeProcessor {
	public function process() {
		// process metacode markers first
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacodeMarker());
		
		// handle static converters
		$this->invokeHtmlNode(new HtmlInputNodeWoltlabMetacode());
	}
}
