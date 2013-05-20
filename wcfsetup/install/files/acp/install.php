<?php
namespace wcf\acp;
use wcf\data\language\LanguageEditor;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\cache\CacheHandler;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\session\SessionHandler;
use wcf\system\template\ACPTemplateEngine;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// change the priority of the PIP's to "1"
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	priority = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// reset sessions
SessionHandler::resetSessions();

// update acp templates
$sql = "UPDATE	wcf".WCF_N."_acp_template
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update language
$sql = "UPDATE	wcf".WCF_N."_language_item
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update installation logs
$sql = "UPDATE	wcf".WCF_N."_package_installation_file_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

$sql = "UPDATE	wcf".WCF_N."_package_installation_sql_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update pips
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// group options
$sql = "UPDATE	wcf".WCF_N."_user_group_option
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// reset all caches
CacheHandler::getInstance()->flushAll();

// delete language files
LanguageEditor::deleteLanguageFiles();

// delete all compiled templates
ACPTemplateEngine::deleteCompiledTemplates(WCF_DIR.'acp/templates/compiled/');

// get server timezone
if ($timezone = @date_default_timezone_get()) {
	if ($timezone != 'Europe/London' && in_array($timezone, DateUtil::getAvailableTimezones())) {
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = ?
			WHERE	optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($timezone, 'timezone'));
	}
}

// set dashboard default values
DashboardHandler::setDefaultValues('com.woltlab.wcf.user.DashboardPage', array(
	// content
	'com.woltlab.wcf.user.recentActivity' => 1,
	// sidebar
	'com.woltlab.wcf.user.registerButton' => 1,
	'com.woltlab.wcf.user.signedInAs' => 2,
	'com.woltlab.wcf.user.statsSidebar' => 3
));
DashboardHandler::setDefaultValues('com.woltlab.wcf.user.MembersListPage', array(
	'com.woltlab.wcf.user.newestMembers' => 1,
	'com.woltlab.wcf.user.mostActiveMembers' => 2
));

// update administrator user rank and user online marking
$editor = new UserEditor(WCF::getUser());
$action = new UserProfileAction(array($editor), 'updateUserRank');
$action->executeAction();
$action = new UserProfileAction(array($editor), 'updateUserOnlineMarking');
$action->executeAction();
