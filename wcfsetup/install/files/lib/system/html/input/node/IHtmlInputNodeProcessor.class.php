<?php
namespace wcf\system\html\input\node;
use wcf\system\html\node\IHtmlNodeProcessor;

/**
 * @since 3.0
 */
interface IHtmlInputNodeProcessor extends IHtmlNodeProcessor {
	public function getEmbeddedContent();
	
	public function process();
}
