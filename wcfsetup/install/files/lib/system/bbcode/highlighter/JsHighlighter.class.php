<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of JavaScript code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class JsHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array("(", ")", "{", "}", "[", "]", ";", ".", ",");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$operators
	 */
	protected $operators = array("=", ">", "<", "!", "~", "?", ":", "==", "<=", ">=", "!=",
		"&&", "||", "++", "--", "+", "-", "*", "/", "&", "|", "^", "%", "<<", ">>", ">>>", "+=", "-=", "*=",
		"/=", "&=", "|=", "^=", "%=", "<<=", ">>=", ">>>=");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
		"String",
		"Array",
		"RegExp",
		"Function",
		"Math",
		"Number",
		"Date",
		"Image",
		"window",
		"document",
		"navigator",
		"onAbort",
		"onBlur",
		"onChange",
		"onClick",
		"onDblClick",
		"onDragDrop",
		"onError",
		"onFocus",
		"onKeyDown",
		"onKeyPress",
		"onKeyUp",
		"onLoad",
		"onMouseDown",
		"onMouseOver",
		"onMouseOut",
		"onMouseMove",
		"onMouseUp",
		"onMove",
		"onReset",
		"onResize",
		"onSelect",
		"onSubmit",
		"onUnload"
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		"break",
		"continue",
		"do",
		"while",
		"export",
		"for",
		"in",
		"if",
		"else",
		"import",
		"return",
		"label",
		"switch",
		"case",
		"var",
		"with",
		"delete",
		"new",
		"this",
		"typeof",
		"void",
		"abstract",
		"boolean",
		"byte",
		"catch",
		"char",
		"class",
		"const",
		"debugger",
		"default",
		"double",
		"enum",
		"extends",
		"false",
		"final",
		"finally",
		"float",
		"function",
		"implements",
		"goto",
		"instanceof",
		"int",
		"interface",
		"long",
		"native",
		"null",
		"package",
		"private",
		"protected",
		"public",
		"short",
		"static",
		"super",
		"synchronized",
		"throw",
		"throws",
		"transient",
		"true",
		"try",
		"volatile"
	);
}
