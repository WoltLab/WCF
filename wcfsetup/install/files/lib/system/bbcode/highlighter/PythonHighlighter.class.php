<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of Python sourcecode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class PythonHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $separators = ['(', ')',/* from __future__ import braces '{', '}', */'[', ']', ';', '.', ',', ':'];
	
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
	protected $operators = ['+=', '-=', '**=', '*=', '//=', '/=', '%=', '~=', '+', '-', '**', '*', '//', '/', '%', 
					'&=', '<<=', '>>=', '^=', '~', '&', '^', '|', '<<', '>>', '=', '!=', '<', '>', '<=', '>='];
	
	/**
	 * @inheritDoc
	 */
	protected $quotes = [["r'", "'"], ["u'", "'"], ['r"', '"'], ['u"', '"'], "'", '"'];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords1 = [
		'print',
		'del',
		'str',
		'int',
		'len',
		'repr',
		'range',
		'raise',
		'pass',
		'continue',
		'break',
		'return'
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords2 = [
		'if',
		'elif',
		'else',
		'try',
		'except',
		'finally',
		'for',
		'in',
		'while'
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords3 = [
		'from',
		'import',
		'as',
		'class',
		'def'
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords4 = [
		'__name__',
		'__init__',
		'__str__',
		'__del__',
		'self',
		'True',
		'False',
		'None',
		'and',
		'or',
		'not',
		'is'
	];
}
