<?php
namespace wcf\system\bbcode;
use wcf\system\bbcode\highlighter\BashHighlighter;
use wcf\system\bbcode\highlighter\CHighlighter;
use wcf\system\bbcode\highlighter\DiffHighlighter;
use wcf\system\bbcode\highlighter\HtmlHighlighter;
use wcf\system\bbcode\highlighter\JavaHighlighter;
use wcf\system\bbcode\highlighter\JsHighlighter;
use wcf\system\bbcode\highlighter\PerlHighlighter;
use wcf\system\bbcode\highlighter\PhpHighlighter;
use wcf\system\bbcode\highlighter\PlainHighlighter;
use wcf\system\bbcode\highlighter\PythonHighlighter;
use wcf\system\bbcode\highlighter\SqlHighlighter;
use wcf\system\bbcode\highlighter\TexHighlighter;
use wcf\system\bbcode\highlighter\XmlHighlighter;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [code] bbcode tag.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class CodeBBCode extends AbstractBBCode {
	/**
	 * code type attribute value
	 * @var	string
	 */
	protected $codeType = '';
	
	/**
	 * file name attribute value
	 * @var	string
	 */
	protected $filename = '';
	
	/**
	 * start line numer attribute value
	 * @var	string
	 */
	protected $startLineNumber = 1;
	
	/**
	 * already used ids for line numbers to prevent duplicate ids in the output
	 * @var	string[]
	 */
	private static $codeIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		// encode html
		$content = self::trim($content);
		
		// get attributes
		$this->mapAttributes($openingTag);
		
		// fetch highlighter-classname
		$className = PlainHighlighter::class;
		
		// no highlighting for strings over a certain size, to prevent DoS
		// this serves as a safety net in case one of the regular expressions
		// in a highlighter causes PCRE to exhaust resources, such as the stack
		if (strlen($content) < 16384) {
			if ($this->codeType) {
				$className = '\wcf\system\bbcode\highlighter\\'.StringUtil::firstCharToUpperCase(mb_strtolower($this->codeType)).'Highlighter';
				
				switch (mb_substr($className, strlen('\wcf\system\bbcode\highlighter\\'))) {
					case 'ShellHighlighter':
						$className = BashHighlighter::class;
					break;
					
					case 'C++Highlighter':
						$className = CHighlighter::class;
					break;
					
					case 'JavascriptHighlighter':
						$className = JsHighlighter::class;
					break;
					
					case 'LatexHighlighter':
						$className = TexHighlighter::class;
					break;
				}
			}
			else {
				// try to guess highlighter
				if (mb_strpos($content, '<?php') !== false) {
					$className = PhpHighlighter::class;
				}
				else if (mb_strpos($content, '<html') !== false) {
					$className = HtmlHighlighter::class;
				}
				else if (mb_strpos($content, '<?xml') === 0) {
					$className = XmlHighlighter::class;
				}
				else if (	mb_strpos($content, 'SELECT') === 0
						||	mb_strpos($content, 'UPDATE') === 0
						||	mb_strpos($content, 'INSERT') === 0
						||	mb_strpos($content, 'DELETE') === 0) {
					$className = SqlHighlighter::class;
				}
				else if (mb_strpos($content, 'import java.') !== false) {
					$className = JavaHighlighter::class;
				}
				else if (	mb_strpos($content, "---") !== false
						&&	mb_strpos($content, "\n+++") !== false) {
					$className = DiffHighlighter::class;
				}
				else if (mb_strpos($content, "\n#include ") !== false) {
					$className = CHighlighter::class;
				}
				else if (mb_strpos($content, '#!/usr/bin/perl') === 0) {
					$className = PerlHighlighter::class;
				}
				else if (mb_strpos($content, 'def __init__(self') !== false) {
					$className = PythonHighlighter::class;
				}
				else if (Regex::compile('^#!/bin/(ba|z)?sh')->match($content)) {
					$className = BashHighlighter::class;
				}
				else if (mb_strpos($content, '\\documentclass') !== false) {
					$className = TexHighlighter::class;
				}
			}
		}
		
		if (!class_exists($className)) {
			$className = PlainHighlighter::class;
		}
		
		if ($parser->getOutputType() == 'text/html') {
			/** @noinspection PhpUndefinedMethodInspection */
			$highlightedContent = self::fixMarkup(explode("\n", $className::getInstance()->highlight($content)));
			
			// show template
			/** @noinspection PhpUndefinedMethodInspection */
			WCF::getTPL()->assign([
				'lineNumbers' => self::makeLineNumbers($content, $this->startLineNumber),
				'startLineNumber' => $this->startLineNumber,
				'content' => $highlightedContent,
				'highlighter' => $className::getInstance(),
				'filename' => $this->filename,
				'lines' => substr_count($content, "\n") + 1
			]);
			return WCF::getTPL()->fetch('codeBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->getDynamicVariable('wcf.bbcode.code.text', [
				'highlighterTitle' => $className::getInstance()->getTitle(),
				'lines' => substr_count($content, "\n") + 1
			]);
		}
	}
	
	/**
	 * Maps the arguments to the property they represent.
	 * 
	 * @param	array		$openingTag
	 */
	protected function mapAttributes(array $openingTag) {
		// reset default values
		$this->codeType = '';
		$this->startLineNumber = 1;
		$this->filename = '';
		
		if (!isset($openingTag['attributes'])) {
			return;
		}
		
		$attributes = $openingTag['attributes'];
		switch (count($attributes)) {
			case 1:
				if (is_numeric($attributes[0])) {
					$this->startLineNumber = intval($attributes[0]);
				}
				else if (mb_strpos($attributes[0], '.') === false) {
					$this->codeType = $attributes[0];
				}
				else {
					$this->filename = $attributes[0];
				}
			break;
			
			case 2:
				if (is_numeric($attributes[0])) {
					$this->startLineNumber = intval($attributes[0]);
					if (mb_strpos($attributes[1], '.') === false) {
						$this->codeType = $attributes[1];
					}
					else {
						$this->filename = $attributes[1];
					}
				}
				else {
					$this->codeType = $attributes[0];
					$this->filename = $attributes[1];
				}
			break;
			
			default:
				$this->codeType = $attributes[0];
				$this->startLineNumber = intval($attributes[1]);
				$this->filename = $attributes[2];
			break;
		}
		
		// correct illegal line number
		if ($this->startLineNumber < 1) {
			$this->startLineNumber = 1;
		}
	}
	
	/**
	 * Returns a string with all line numbers
	 * 
	 * @param	string		$code
	 * @param	integer		$start
	 * @param	string		$split
	 * @return	string
	 */
	protected static function makeLineNumbers($code, $start, $split = "\n") {
		$lines = explode($split, $code);
		
		$lineNumbers = [];
		$i = -1;
		// find an unused codeID
		do {
			$codeID = mb_substr(StringUtil::getHash($code), 0, 6).(++$i ? '_'.$i : '');
		}
		while (isset(self::$codeIDs[$codeID]));
		
		// mark codeID as used
		self::$codeIDs[$codeID] = true;
		
		for ($i = $start, $j = count($lines) + $start; $i < $j; $i++) {
			$lineNumbers[$i] = 'codeLine_'.$i.'_'.$codeID;
		}
		return $lineNumbers;
	}
	
	/**
	 * Removes empty lines from the beginning and end of a string.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	protected static function trim($string) {
		$string = preg_replace('/^\s*\n/', '', $string);
		$string = preg_replace('/\n\s*$/', '', $string);
		return $string;
	}
	
	/**
	 * Fixes markup that every line has proper number of opening and closing tags
	 * 
	 * @param	string[]	$lines
	 * @return	string[]
	 */
	public static function fixMarkup(array $lines) {
		static $spanRegex = null;
		static $emptyTagRegex = null;
		if ($spanRegex === null) {
			$spanRegex = new Regex('(?:<span(?: class="(?:[^"])*")?>|</span>)');
			$emptyTagRegex = new Regex('<span(?: class="(?:[^"])*")?></span>');
		}
		
		$openTags = [];
		foreach ($lines as &$line) {
			$spanRegex->match($line, true);
			// open all tags again
			$line = implode('', $openTags).$line;
			$matches = $spanRegex->getMatches();
			
			// parse opening and closing spans
			foreach ($matches[0] as $match) {
				if ($match === '</span>') array_pop($openTags);
				else {
					array_push($openTags, $match);
				}
			}
			
			// close all tags
			$line .= str_repeat('</span>', count($openTags));
			
			// remove empty tags to avoid cluttering the output
			$line = $emptyTagRegex->replace($line, '');
		}
		unset($line);
		return $lines;
	}
}
