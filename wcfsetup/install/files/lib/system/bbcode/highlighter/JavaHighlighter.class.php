<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of java source code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class JavaHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $separators = ["(", ")", "{", "}", "[", "]", ";", ".", ",", "<", ">"];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords2 = [
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
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords3 = [
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
	];
}
