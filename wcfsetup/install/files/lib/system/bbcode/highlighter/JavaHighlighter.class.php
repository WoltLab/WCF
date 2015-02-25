<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of java source code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class JavaHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array("(", ")", "{", "}", "[", "]", ";", ".", ",", "<", ">");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'package',
		'abstract',
		'break',
		'case',
		'catch',
		'class',
		'continue',
		'default',
		'do',
		'else',
		'extends',
		'false',
		'finally',
		'for',
		'goto',
		'if',
		'implements',
		'instanceof',
		'interface',
		'native',
		'new',
		'null',
		'private',
		'protected',
		'public',
		'return',
		'super',
		'strictfp',
		'switch',
		'synchronized',
		'this',
		'throws',
		'throw',
		'transient',
		'true',
		'try',
		'volatile',
		'while',
		'boolean',
		'byte',
		'char',
		'const',
		'double',
		'final',
		'float',
		'int',
		'long',
		'short',
		'static',
		'void',
		'import'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'Boolean',
		'Float',
		'Character',
		'Double',
		'Enum',
		'Long',
		'Short',
		'Integer',
		'Math',
		'Object',
		'String',
		'StringBuffer',
		'System',
		'Thread',
		'Exception',
		'Throwable',
		'Runnable',
		'Appendable',
		'Cloneable',
		'HashMap',
		'List',
		'ArrayList'
	);
}
