<?php
namespace wcf\system\html\node;

/**
 * @since 3.0
 */
interface IHtmlNodeProcessor {
	/**
	 * @return      \DOMDocument
	 */
	public function getDocument();
	
	public function getHtml();
	
	public function load($html);
}
