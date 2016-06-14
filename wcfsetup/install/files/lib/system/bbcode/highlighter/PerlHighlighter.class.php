<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of Perl sourcecode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class PerlHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $separators = ['(', ')', '{', '}', '[', ']', ';', '.', ','];
	
	/**
	 * @inheritDoc
	 */
	protected $singleLineComment = ['#'];
	
	/**
	 * @inheritDoc
	 */
	protected $commentStart = [];
	
	/**
	 * @inheritDoc
	 */
	protected $commentEnd = [];
	
	/**
	 * @inheritDoc
	 */
	protected $operators = ['.=', '=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=',
		'&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%', '<<', '>>', '>>>', '+=', '-=', '*=',
		'/=', '&=', '|=', '^=', '%=', '<<=', '>>=', '>>>=', '->', '::'];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords1 = [
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
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords2 = [
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
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords3 = [
		'use',
		'import',
		'require',
		'sub'
	];
}
