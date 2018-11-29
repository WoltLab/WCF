<?php
namespace wcf\system\html\output\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Processes code listings.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodePre extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'pre';
	
	/**
	 * already used ids for line numbers to prevent duplicate ids in the output
	 * @var	string[]
	 */
	private static $codeIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			if ($element->getAttribute('class') === 'woltlabHtml') {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['rawHTML' => $element->textContent]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
				continue;
			}
			
			switch ($this->outputType) {
				case 'text/html':
					$nodeIdentifier = StringUtil::getRandomID();
					$context = $htmlNodeProcessor->getHtmlProcessor()->getContext();
					$prefix = '';
					// Create a unique prefix if possible
					if (isset($context['objectType']) && isset($context['objectID'])) {
						$prefix = $context['objectType'].'_'.$context['objectID'].'_';
					}
					$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
						'content' => $element->textContent,
						'file' => $element->getAttribute('data-file'),
						'highlighter' => $element->getAttribute('data-highlighter'),
						'line' => $element->hasAttribute('data-line') ? $element->getAttribute('data-line') : 1,
						'skipInnerContent' => true,
						'prefix' => $prefix
					]);
					
					$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
					break;
				
				case 'text/simplified-html':
				case 'text/plain':
					$htmlNodeProcessor->replaceElementWithText(
						$element,
						WCF::getLanguage()->getDynamicVariable('wcf.bbcode.code.simplified', ['lines' => substr_count($element->nodeValue, "\n") + 1]),
						true
					);
					break;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		// HTML bbcode
		if (isset($data['rawHTML'])) {
			return $data['rawHTML'];
		}
		
		$content = preg_replace('/^\s*\n/', '', $data['content']);
		$content = preg_replace('/\n\s*$/', '', $content);
		
		$file = $data['file'];
		$highlighter = $data['highlighter'];
		$line = ($data['line'] < 1) ? 1 : $data['line'];
		
		switch ($highlighter) {
			case 'js':
				$highlighter = 'javascript';
				break;
			case 'c++':
				$highlighter = 'cpp';
				break;
			case 'tex':
				$highlighter = 'latex';
				break;
			case 'shell':
				$highlighter = 'bash';
				break;
		}
		
		if (!$highlighter) {
			// try to guess highlighter
			if (mb_strpos($content, '<?php') !== false) {
				$highlighter = 'php';
			}
			else if (mb_strpos($content, '<html') !== false) {
				$highlighter = 'html';
			}
			else if (mb_strpos($content, '<?xml') === 0) {
				$highlighter = 'xml';
			}
			else if (	mb_strpos($content, 'SELECT') === 0
				||	mb_strpos($content, 'UPDATE') === 0
				||	mb_strpos($content, 'INSERT') === 0
				||	mb_strpos($content, 'DELETE') === 0) {
				$highlighter = 'sql';
			}
			else if (mb_strpos($content, 'import java.') !== false) {
				$highlighter = 'java';
			}
			else if (	mb_strpos($content, "---") !== false
				&&	mb_strpos($content, "\n+++") !== false) {
				$highlighter = 'diff';
			}
			else if (mb_strpos($content, "\n#include ") !== false) {
				$highlighter = 'c';
			}
			else if (mb_strpos($content, '#!/usr/bin/perl') === 0) {
				$highlighter = 'perl';
			}
			else if (mb_strpos($content, 'def __init__(self') !== false) {
				$highlighter = 'python';
			}
			else if (Regex::compile('^#!/bin/(ba|z)?sh')->match($content)) {
				$highlighter = 'bash';
			}
			else if (mb_strpos($content, '\\documentclass') !== false) {
				$highlighter = 'latex';
			}
		}
		
		$meta = BBCodeHandler::getInstance()->getHighlighterMeta();
		$title = WCF::getLanguage()->get('wcf.bbcode.code');
		if (isset($meta[$highlighter])) {
			$title = $meta[$highlighter]['title'];
		}
		else {
			$highlighter = null;
		}

		$splitContent = explode("\n", $content);
		$last = array_pop($splitContent);
		$splitContent = array_map(function ($item) {
			return $item."\n";
		}, $splitContent);
		$splitContent[] = $last;
		
		// show template
		/** @noinspection PhpUndefinedMethodInspection */
		WCF::getTPL()->assign([
			'codeID' => $this->getCodeID($data['prefix'] ?? '', $content),
			'startLineNumber' => $line,
			'content' => $splitContent,
			'language' => $highlighter,
			'filename' => $file,
			'title' => $title,
			'lines' => count($splitContent)
		]);
		
		return WCF::getTPL()->fetch('codeMetaCode');
	}
	
	/**
	 * Returns a unique ID for this code block.
	 *
	 * @param	string		$code
	 * @return	string
	 */
	protected function getCodeID($prefix, $code) {
		$i = -1;
		// find an unused codeID
		do {
			$codeID = $prefix.mb_substr(StringUtil::getHash($code), 0, 6).(++$i ? '_'.$i : '');
		}
		while (isset(self::$codeIDs[$codeID]));
		
		// mark codeID as used
		self::$codeIDs[$codeID] = true;
		
		return $codeID;
	}
}
