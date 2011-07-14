<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\template\TemplatePluginModifier;
use wcf\util\StringUtil;

/**
 * The 'truncate' modifier truncates a string.
 * 
 * Usage:
 * {$foo|truncate:35:'...'}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierTruncate implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// default values
		$length = 80;
		$etc = '...';
		$breakWords = false;
		
		// get values
		$string = $tagArgs[0];
		if (isset($tagArgs[1])) $length = intval($tagArgs[1]);
		if (isset($tagArgs[2])) $etc = $tagArgs[2];
		if (isset($tagArgs[3])) $breakWords = $tagArgs[3];
		
		// execute plugin
		if ($length == 0) {
			return '';
		}

		if (StringUtil::length($string) > $length) {
			$length -= StringUtil::length($etc);
			
			if (!$breakWords) {
				$string = preg_replace('/\s+?(\S+)?$/', '', StringUtil::substring($string, 0, $length + 1));
			}
  
			return StringUtil::substring($string, 0, $length).$etc;
		}
		else {
   			return $string;
		}
	}
}
?>