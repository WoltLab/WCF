<?php
namespace wcf\util;
use wcf\system\style\StyleCompiler;

/**
 * Contains Style-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class StyleUtil {
	/**
	 * Converts css code from LTR to RTL.
	 * 
	 * @param	string		$contents
	 * @return	string
	 */
	public static function convertCSSToRTL($contents) {
		// parse style attributes
		// background
		// background-position
		$contents = preg_replace('/background-position:\s*left/', 'wcf-background-position:left', $contents);
		$contents = preg_replace('/background-position:\s*right/', 'background-position:left', $contents);
		$contents = str_replace('wcf-background-position:left', 'background-position:right', $contents);
		$contents = preg_replace_callback('/background-position:\s*([\d\.]+)%/', function ($matches) {
			return 'background-position:'.(100.0-$matches[1]).'%';
		}, $contents);
		
		// background-image
		$contents = str_replace('-ltr', '-rtl', $contents);
		
		// (border, margin, padding) left / right
		$contents = str_replace('left:', 'wcf-left:', $contents);
		$contents = str_replace('right:', 'left:', $contents);
		$contents = str_replace('wcf-left:', 'right:', $contents);
		
		// border-width
		$contents = preg_replace('/border-width:\s*([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)/', 'border-width:\\1 \\4 \\3 \\2', $contents);
		
		// (border-left-width, border-right-width)
		$contents = str_replace('border-left-width:', 'wcf-border-left-width:', $contents);
		$contents = str_replace('border-right-width:', 'border-left-width:', $contents);
		$contents = str_replace('wcf-border-left-width:', 'border-right-width:', $contents);
		
		// clear
		$contents = preg_replace('/clear:\s*left/', 'wcf-clear:left', $contents);
		$contents = preg_replace('/clear:\s*right/', 'clear:left', $contents);
		$contents = str_replace('wcf-clear:left', 'clear:right', $contents);
		
		// todo: clip?
		
		// float
		$contents = preg_replace('/float:\s*left/', 'wcf-float:left', $contents);
		$contents = preg_replace('/float:\s*right/', 'float:left', $contents);
		$contents = str_replace('wcf-float:left', 'float:right', $contents);
		
		// margin
		$contents = preg_replace('/margin:\s*([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)/', 'margin:\\1 \\4 \\3 \\2', $contents);
		
		// padding
		$contents = preg_replace('/padding:\s*([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)\s+([^\s;\}]+)/', 'padding:\\1 \\4 \\3 \\2', $contents);
		
		// text-align
		$contents = preg_replace('/text-align:\s*left/', 'wcf-text-align:left', $contents);
		$contents = preg_replace('/text-align:\s*right/', 'text-align:left', $contents);
		$contents = str_replace('wcf-text-align:left', 'text-align:right', $contents);
		
		// text-shadow
		$contents = preg_replace('/text-shadow:\s*(\d)/', 'text-shadow:-\\1', $contents);
		
		return $contents;
	}
	
	/**
	 * Compresses css code.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function compressCSS($string) {
		$string = StringUtil::unifyNewlines($string);
		// remove comments
		$string = preg_replace('!/\*.*?\*/\r?\n?!s', '', $string);
		// remove tabs
		$string = preg_replace('!\t+!', '', $string);
		// remove line breaks
		$string = preg_replace('!(?<=\{|;) *\n!', '', $string);
		$string = preg_replace('! *\n(?=})!', '', $string);
		// remove empty lines
		$string = preg_replace('~\n{2,}~s', "\n", $string);
		
		return StringUtil::trim($string);
	}
	
	/**
	 * Updates the acp style file.
	 */
	public static function updateStyleFile() {
		StyleCompiler::getInstance()->compileACP();
	}
	
	private function __construct() { }
}
