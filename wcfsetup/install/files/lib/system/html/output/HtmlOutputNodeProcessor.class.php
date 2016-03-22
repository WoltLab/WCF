<?php
namespace wcf\system\html\output;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\html\output\node\HtmlOutputNodeBlockquote;
use wcf\system\html\output\node\HtmlOutputNodeWoltlabMention;
use wcf\system\html\output\node\IHtmlOutputNode;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeProcessor extends HtmlNodeProcessor {
	protected $nodeData = [];
	
	public function load($html) {
		parent::load($html);
		
		$this->nodeData = [];
	}
	
	public function process() {
		// TODO: this should be dynamic to some extent
		$quoteNode = new HtmlOutputNodeBlockquote();
		$quoteNode->process($this);
		
		$woltlabMentionNode = new HtmlOutputNodeWoltlabMention();
		$woltlabMentionNode->process($this);
	}
	
	public function getHtml() {
		$html = parent::getHtml();
		
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
	
	public function addNodeData(IHtmlOutputNode $htmlOutputNode, $nodeIdentifier, array $data) {
		$this->nodeData[] = [
			'data' => $data,
			'identifier' => $nodeIdentifier,
			'object' => $htmlOutputNode
		];
	}
}
