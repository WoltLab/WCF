<?php
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// change the priority of the PIPs to "1"
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	priority = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// reset sessions
SessionHandler::resetSessions();

// update acp templates
$sql = "UPDATE	wcf".WCF_N."_acp_template
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// update language
$sql = "UPDATE	wcf".WCF_N."_language_item
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// update installation logs
$sql = "UPDATE	wcf".WCF_N."_package_installation_file_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

$sql = "UPDATE	wcf".WCF_N."_package_installation_sql_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// update pips
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// group options
$sql = "UPDATE	wcf".WCF_N."_user_group_option
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// get server timezone
if ($timezone = @date_default_timezone_get()) {
	if ($timezone != 'Europe/London' && in_array($timezone, DateUtil::getAvailableTimezones())) {
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = ?
			WHERE	optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$timezone,
			'timezone'
		]);
	}
}
