<?php
// wcf imports
require_once(WCF_DIR.'lib/system/template/TemplateScriptingCompiler.class.php');

/**
 * Compiles template scripting in notification messages.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class UserNotificationMessageCompiler extends TemplateScriptingCompiler {
	public $rightDelimiter = '}}';
	public $leftDelimiter = '{{';
}
?>