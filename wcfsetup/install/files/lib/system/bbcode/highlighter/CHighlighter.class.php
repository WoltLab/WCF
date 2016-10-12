<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of c / c++ source code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class CHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $separators = ['(', ')', '{', '}', '[', ']', ';', '.', ','];
	
	/**
	 * @inheritDoc
	 */
	protected $operators = ['=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=',
		'&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%', '<<', '>>', '>>>', '+=', '-=', '*=',
		'/=', '&=', '|=', '^=', '%=', '<<=', '>>=', '>>>='];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords1 = [
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
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords2 = [
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
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords3 = [
		'#include',
		'#define',
		'#if',
		'#else',
		'#ifdef',
		'#endif'
	];
}
