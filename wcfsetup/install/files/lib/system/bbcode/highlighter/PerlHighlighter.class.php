<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of Perl sourcecode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class PerlHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array('(', ')', '{', '}', '[', ']', ';', '.', ',');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$singleLineComment
	 */
	protected $singleLineComment = array('#');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$commentStart
	 */
	protected $commentStart = array();
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$commentEnd
	 */
	protected $commentEnd = array();
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$operators
	 */
	protected $operators = array('.=', '=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=',
		'&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%', '<<', '>>', '>>>', '+=', '-=', '*=',
		'/=', '&=', '|=', '^=', '%=', '<<=', '>>=', '>>>=', '->', '::');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
		'print',
		'sprintf',
		'length',
		'substr',
		'eval',
		'die',
		'opendir',
		'closedir',
		'open',
		'close',
		'chmod',
		'unlink',
		'flock',
		'read',
		'seek',
		'stat',
		'truncate',
		'chomp',
		'localtime',
		'defined',
		'undef',
		'uc',
		'lc',
		'trim',
		'split',
		'sort',
		'keys',
		'push',
		'pop',
		'join',
		'local',
		'select',
		'index',
		'sqrt',
		'system',
		'crypt',
		'pack',
		'unpack'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'case',
		'do',
		'while',
		'for',
		'if',
		'foreach',
		'my',
		'else',
		'elsif',
		'eq',
		'ne',
		'or',
		'xor',
		'and',
		'lt',
		'gt',
		'ge',
		'le',
		'return',
		'last',
		'goto',
		'unless',
		'given',
		'when',
		'default',
		'until',
		'break',
		'exit'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'use',
		'import',
		'require',
		'sub'
	);
}
