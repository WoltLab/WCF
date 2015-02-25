<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of c / c++ source code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class CHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array('(', ')', '{', '}', '[', ']', ';', '.', ',');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$operators
	 */
	protected $operators = array('=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=',
		'&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%', '<<', '>>', '>>>', '+=', '-=', '*=',
		'/=', '&=', '|=', '^=', '%=', '<<=', '>>=', '>>>=');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
		'and',
		'and_eq',
		'asm',
		'bitand',
		'bitor',
		'break',
		'case',
		'catch',
		'compl',
		'const_cast',
		'continue',
		'default',
		'delete',
		'do',
		'dynamic_cast',
		'else',
		'for',
		'fortran',
		'friend',
		'goto',
		'if',
		'new',
		'not',
		'not_eq',
		'operator',
		'or',
		'or_eq',
		'private',
		'protected',
		'public',
		'reinterpret_cast',
		'return',
		'sizeof',
		'static_cast',
		'switch',
		'this',
		'throw',
		'try',
		'typeid',
		'using',
		'while',
		'xor',
		'xor_eq'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'auto',
		'bool',
		'char',
		'class',
		'const',
		'double',
		'enum',
		'explicit',
		'export',
		'extern',
		'float',
		'inline',
		'int',
		'long',
		'mutable',
		'namespace',
		'register',
		'short',
		'signed',
		'static',
		'struct',
		'template',
		'typedef',
		'typename',
		'union',
		'unsigned',
		'virtual',
		'void',
		'volatile',
		'wchar_t'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'#include',
		'#define',
		'#if',
		'#else',
		'#ifdef',
		'#endif'
	);
}
