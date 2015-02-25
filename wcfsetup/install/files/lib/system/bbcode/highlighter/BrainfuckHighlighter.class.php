<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of brainfuck.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class BrainfuckHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlight()
	 */
	public function highlight($string) {
		$string = preg_replace('/[^-\\+\\.,\\[\\]\\>\\<]+/', '||span class="hlComments"||\\0||/span||', $string);
		$string = preg_replace('/[\\<\\>]+/', '<span class="hlKeywords4">\\0</span>', $string);
		$string = preg_replace('/[-\\+]+/', '<span class="hlKeywords1">\\0</span>', $string);
		$string = preg_replace('/[\\.,]+/', '<span class="hlKeywords2">\\0</span>', $string);
		$string = preg_replace('/[\\[\\]]+/', '<span class="hlKeywords3">\\0</span>', $string);
		
		$string = str_replace(array('||span class="hlComments"||', '||/span||'), array('<span class="hlComments">', '</span>'), $string);
		return $string;
	}
}
