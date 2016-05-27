<?php
namespace wcf\system\html\node;

/**
 * TOOD documentation
 * @since	2.2
 */
abstract class AbstractHtmlNode implements IHtmlNode {
	protected $tagName = '';
	
	const PLACEHOLDER = '<!-- META_CODE_INNER_CONTENT -->';
	
	public function getTagName() {
		return $this->tagName;
	}
	
	public function replaceTag(array $data) {
		throw new \BadMethodCallException("Method replaceTag() is not supported by ".get_class($this));
	}
}
