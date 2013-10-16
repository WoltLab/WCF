<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of Python sourcecode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class PythonHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array('(', ')',/* from __future__ import braces '{', '}', */'[', ']', ';', '.', ',', ':');
	
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
	protected $operators = array('+=', '-=', '**=', '*=', '//=', '/=', '%=', '~=', '+', '-', '**', '*', '//', '/', '%', 
					'&=', '<<=', '>>=', '^=', '~', '&', '^', '|', '<<', '>>', '=', '!=', '<', '>', '<=', '>=');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$quotes
	 */
	protected $quotes = array(array("r'", "'"), array("u'", "'"), array('r"', '"'), array('u"', '"'), "'", '"');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
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
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'if',
		'elif',
		'else',
		'try',
		'except',
		'finally',
		'for',
		'in',
		'while'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'from',
		'import',
		'as',
		'class',
		'def'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords4
	 */
	protected $keywords4 = array(
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
	);
}
