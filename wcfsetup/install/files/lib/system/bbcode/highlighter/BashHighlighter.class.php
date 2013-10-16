<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of bash scripts.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class BashHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array(';', '=');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$quotes
	 */
	protected $quotes = array('"', "'", '`');
	
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
	protected $operators = array('||', '&&', '&', '|', '<<=', '>>=', '<<', '+=', '-=', '*=', '/=', '%=',
					'-gt', '-lt', '-n', '-a', '-o',
					'+', '-', '*', '/', '%', '<', '?', ':', '==', '!=', '=', '!', '>', '2>', '>>');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
		'true',
		'false'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'if',
		'then',
		'else',
		'fi',
		'for',
		'until',
		'while',
		'do',
		'done',
		'case',
		'in',
		'esac'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'echo',
		'exit',
		'unset',
		'read',
		'[', ']', 'test',
		'let',
		'sed',
		'grep',
		'awk'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords4
	 */
	protected $keywords4 = array(
		'$?'
	);
}
