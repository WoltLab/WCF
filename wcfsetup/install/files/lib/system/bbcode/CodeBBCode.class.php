<?php
namespace wcf\system\bbcode;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [code] bbcode tag.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
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
	 * @var	array<string>
	 */
	private static $codeIDs = array();
	
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		// encode html
		$content = self::trim($content);
		
		// get attributes
		$this->mapAttributes($openingTag);
		
		// fetch highlighter-classname
		$className = '\wcf\system\bbcode\highlighter\PlainHighlighter';
		
		// no highlighting for strings over a certain size, to prevent DoS
		// this serves as a safety net in case one of the regular expressions
		// in a highlighter causes PCRE to exhaust resources, such as the stack
		if (strlen($content) < 16384) {
			if ($this->codeType) {
				$className = '\wcf\system\bbcode\highlighter\\'.StringUtil::firstCharToUpperCase(mb_strtolower($this->codeType)).'Highlighter';
				
				switch (mb_substr($className, strlen('\wcf\system\bbcode\highlighter\\'))) {
					case 'ShellHighlighter':
						$className = '\wcf\system\bbcode\highlighter\BashHighlighter';
					break;
					
					case 'C++Highlighter':
						$className = '\wcf\system\bbcode\highlighter\CHighlighter';
					break;
					
					case 'JavascriptHighlighter':
						$className = '\wcf\system\bbcode\highlighter\JsHighlighter';
					break;
					
					case 'LatexHighlighter':
						$className = '\wcf\system\bbcode\highlighter\TexHighlighter';
					break;
				}
			}
			else {
				// try to guess highlighter
				if (mb_strpos($content, '<?php') !== false) {
					$className = '\wcf\system\bbcode\highlighter\PhpHighlighter';
				}
				else if (mb_strpos($content, '<html') !== false) {
					$className = '\wcf\system\bbcode\highlighter\HtmlHighlighter';
				}
				else if (mb_strpos($content, '<?xml') === 0) {
					$className = '\wcf\system\bbcode\highlighter\XmlHighlighter';
				}
				else if (	mb_strpos($content, 'SELECT') === 0
						||	mb_strpos($content, 'UPDATE') === 0
						||	mb_strpos($content, 'INSERT') === 0
						||	mb_strpos($content, 'DELETE') === 0) {
					$className = '\wcf\system\bbcode\highlighter\SqlHighlighter';
				}
				else if (mb_strpos($content, 'import java.') !== false) {
					$className = '\wcf\system\bbcode\highlighter\JavaHighlighter';
				}
				else if (	mb_strpos($content, "---") !== false
						&&	mb_strpos($content, "\n+++") !== false) {
					$className = '\wcf\system\bbcode\highlighter\DiffHighlighter';
				}
				else if (mb_strpos($content, "\n#include ") !== false) {
					$className = '\wcf\system\bbcode\highlighter\CHighlighter';
				}
				else if (mb_strpos($content, '#!/usr/bin/perl') === 0) {
					$className = '\wcf\system\bbcode\highlighter\PerlHighlighter';
				}
				else if (mb_strpos($content, 'def __init__(self') !== false) {
					$className = '\wcf\system\bbcode\highlighter\PythonHighlighter';
				}
				else if (Regex::compile('^#!/bin/(ba|z)?sh')->match($content)) {
					$className = '\wcf\system\bbcode\highlighter\BashHighlighter';
				}
				else if (mb_strpos($content, '\\documentclass') !== false) {
					$className = '\wcf\system\bbcode\highlighter\TexHighlighter';
				}
				else if (Regex::compile('[-\\+\\.,\\[\\]\\>\\<]{9}')->match($content)) {
					// 9 times a brainfuck char in a row -> seems to be brainfuck
					$className = '\wcf\system\bbcode\highlighter\BrainfuckHighlighter';
				}
			}
		}
		
		if (!class_exists($className)) {
			$className = '\wcf\system\bbcode\highlighter\PlainHighlighter';
		}
		
		if ($parser->getOutputType() == 'text/html') {
			$highlightedContent = self::fixMarkup(explode("\n", $className::getInstance()->highlight($content)));
			
			// show template
			WCF::getTPL()->assign(array(
				'lineNumbers' => self::makeLineNumbers($content, $this->startLineNumber),
				'startLineNumber' => $this->startLineNumber,
				'content' => $highlightedContent,
				'highlighter' => $className::getInstance(),
				'filename' => $this->filename,
				'lines' => substr_count($content, "\n") + 1
			));
			return WCF::getTPL()->fetch('codeBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->getDynamicVariable('wcf.bbcode.code.text', array(
				'highlighterTitle' => $className::getInstance()->getTitle(),
				'lines' => substr_count($content, "\n") + 1
			));
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
	 * @return	string
	 */
	protected static function makeLineNumbers($code, $start, $split = "\n") {
		$lines = explode($split, $code);
		
		$lineNumbers = array();
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
	 * @param	array<string>	$lines
	 */
	public static function fixMarkup(array $lines) {
		static $spanRegex = null;
		static $emptyTagRegex = null;
		if ($spanRegex === null) {
			$spanRegex = new Regex('(?:<span(?: class="(?:[^"])*")?>|</span>)');
			$emptyTagRegex = new Regex('<span(?: class="(?:[^"])*")?></span>');
		}
		
		$openTags = array();
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
