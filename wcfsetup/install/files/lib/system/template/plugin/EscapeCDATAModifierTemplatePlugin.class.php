<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * The 'escapeCDATA' modifier escapes the closing CDATA-Tag.
 * 
 * Usage:
 * {$string|escapeCDATA}
 * {"ABC]]>XYZ"|escapeCDATA}
 *
 * @author 	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class EscapeCDATAModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return StringUtil::escapeCDATA($tagArgs[0]);
	}
}
