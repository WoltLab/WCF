<?php
namespace wcf\system\html\output\node;
use wcf\system\html\output\HtmlOutputNodeProcessor;

/**
 * TOOD documentation
 * @since	2.2
 */
interface IHtmlOutputNode {
	public function process(HtmlOutputNodeProcessor $htmlOutputNodeProcessor);
	
	public function replaceTag(array $data);
}
