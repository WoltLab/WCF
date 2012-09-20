<?php
namespace wcf\util;
use wcf\system\io\File;

/**
 * Contains Style-related functions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
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
		
		// clip?

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
		throw new \wcf\system\exception\SystemException("updateStyleFile()");
		// get file handle
		$file = new File(WCF_DIR . 'acp/style/style-ltr.css', 'wb');
		
		// include static styles
		$staticStyles = glob(WCF_DIR.'style/*.css');
		if ($staticStyles) {
			foreach ($staticStyles as $staticStyle) {
				if (!preg_match('/style-\d+(?:-rtl)?\.css/', $staticStyle)) {
					// get style
					$contents = file_get_contents($staticStyle);
					// compress style
					$contents = StyleUtil::compressCSS($contents);
					// fix icon/image path
					$contents = str_replace('../icon/', '../../icon/', $contents);
					$contents = str_replace('../images/', '../../images/', $contents);
					// write style
					$file->write("/* static: ".basename($staticStyle)." */\n");
					$file->write(StringUtil::trim($contents)."\n");
				}
			}
		}
		// include static acp styles
		$staticStyles = glob(WCF_DIR.'acp/style/*.css');
		if ($staticStyles) {
			foreach ($staticStyles as $staticStyle) {
				if (!preg_match('/style-(?:ltr|rtl)\.css/', $staticStyle) && !preg_match('/ie\dFix\.css/', $staticStyle)) {
					$contents = file_get_contents($staticStyle);
					$contents = StyleUtil::compressCSS($contents);
					$file->write("/* static: acp/".basename($staticStyle)." */\n");
					$file->write(StringUtil::trim($contents)."\n");
				}
			}
		}
		// close file
		$file->close();
		@chmod(WCF_DIR . 'acp/style/style-ltr.css', 0777);
		
		// update rtl version
		self::updateStyleFileRTL();
	}
	
	/**
	 * Converts the file of this style to a RTL ("right-to-left") version. 
	 */
	public static function updateStyleFileRTL() {
		throw new \wcf\system\exception\SystemException("updateStyleFileRTL()");
		
		// get contents of LTR version
		$contents = file_get_contents(WCF_DIR . 'acp/style/style-ltr.css');
		
		// convert ltr to rtl
		$contents = StyleUtil::convertCSSToRTL($contents);
		
		// write file
		$file = new File(WCF_DIR . 'acp/style/style-rtl.css');
		$file->write($contents);
		
		// close file
		$file->close();
		@chmod(WCF_DIR . 'acp/style/style-rtl.css', 0777);
	}
	
	private function __construct() { }
}
