<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of brainfuck.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class BrainfuckHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	public function highlight($string) {
		$string = preg_replace('/[^-\\+\\.,\\[\\]\\>\\<]+/', '||span class="hlComments"||\\0||/span||', $string);
		$string = preg_replace('/[\\<\\>]+/', '<span class="hlKeywords4">\\0</span>', $string);
		$string = preg_replace('/[-\\+]+/', '<span class="hlKeywords1">\\0</span>', $string);
		$string = preg_replace('/[\\.,]+/', '<span class="hlKeywords2">\\0</span>', $string);
		$string = preg_replace('/[\\[\\]]+/', '<span class="hlKeywords3">\\0</span>', $string);
		
		$string = str_replace(['||span class="hlComments"||', '||/span||'], ['<span class="hlComments">', '</span>'], $string);
		return $string;
	}
}
