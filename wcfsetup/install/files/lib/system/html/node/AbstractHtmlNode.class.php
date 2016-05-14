<?php
namespace wcf\system\html\node;

abstract class AbstractHtmlNode implements IHtmlNode {
	protected $tagName = '';
	
	public function getTagName() {
		return $this->tagName;
	}
	
	public function replaceTag(array $data) {
		throw new \BadMethodCallException("Method replaceTag() is not supported by ".get_class($this));
	}
}
