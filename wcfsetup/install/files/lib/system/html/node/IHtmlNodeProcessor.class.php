<?php
namespace wcf\system\html\node;

/**
 * @since 2.2
 */
interface IHtmlNodeProcessor {
	public function getDocument();
	
	public function getHtml();
	
	public function load($html);
}
