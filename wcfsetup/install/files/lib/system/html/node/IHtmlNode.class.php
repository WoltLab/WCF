<?php
namespace wcf\system\html\node;

/**
 * TOOD documentation
 * @since	3.0
 */
interface IHtmlNode {
	public function getTagName();
	
	/**
	 * @param \DOMElement[] $elements
	 * @param HtmlNodeProcessor $htmlNodeProcessor
	 * @return mixed
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor);
	
	public function replaceTag(array $data);
}
