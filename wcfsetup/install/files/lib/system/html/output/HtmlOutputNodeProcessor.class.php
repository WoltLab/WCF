<?php
namespace wcf\system\html\output;

use wcf\system\html\output\node\IHtmlOutputNode;
use wcf\system\html\output\node\QuoteHtmlOutputNode;
use wcf\system\WCF;

class HtmlOutputNodeProcessor {
	/**
	 * @var \DOMDocument
	 */
	protected $document;
	
	protected $nodeData = [];
	
	public function load($html) {
		$this->document = new \DOMDocument();
		$this->document->loadHTML($html);
		$this->nodeData = [];
	}
	
	public function process() {
		$quoteNode = WCF::getDIContainer()->get(QuoteHtmlOutputNode::class);
		$quoteNode->process($this);
		
		$html = $this->document->saveHTML();
		
		// remove nuisance added by PHP
		$html = preg_replace('~^<!DOCTYPE[^>]+>\s<html><body>~', '', $html);
		$html = preg_replace('~</body></html>$~', '', $html);
		
		/** @var IHtmlOutputNode $obj */
		foreach ($this->nodeData as $data) {
			$obj = $data['object'];
			$string = $obj->replaceTag($data['data']);
			$html = preg_replace_callback('~<wcfNode-' . $data['identifier'] . '>(?P<content>.*)</wcfNode-' . $data['identifier'] . '>~', function($matches) use ($string) {
				$string = str_replace('<!-- META_CODE_INNER_CONTENT -->', $matches['content'], $string);
				
				return $string;
			}, $html);
			
		}
		
		return $html;
	}
	
	public function getDocument() {
		return $this->document;
	}
	
	public function addNodeData(IHtmlOutputNode $htmlOutputNode, $nodeIdentifier, array $data) {
		$this->nodeData[] = [
			'data' => $data,
			'identifier' => $nodeIdentifier,
			'object' => $htmlOutputNode
		];
	}
	
	public function renameTag(\DOMElement $element, $tagName) {
		$newElement = $this->document->createElement($tagName);
		$element->parentNode->insertBefore($newElement, $element);
		while ($element->hasChildNodes()) {
			$newElement->appendChild($element->firstChild);
		}
		
		$element->parentNode->removeChild($element);
		
		return $newElement;
	}
	
	public function unwrapContent(\DOMElement $element) {
		while ($element->hasChildNodes()) {
			$element->parentNode->insertBefore($element->firstChild, $element);
		}
		
		$element->parentNode->removeChild($element);
	}
}
