<?php

use wcf\system\WCF;

/**
 * Removes the files belonging to the old MailForm in order to make this completely non-functional.
 * 
 * @author	Florian Gail
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

@unlink(WCF_DIR . 'lib/form/MailForm.class.php');
@unlink(WCF_DIR . 'templates/mail.tpl');

// delete file log entry
$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
	WHERE		packageID = ?
			AND filename = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	$this->installation->getPackageID(),
	'lib/form/MailForm.class.php'
]);
$sql = "DELETE FROM	wcf".WCF_N."_template
	WHERE		packageID = ?
			AND templateName = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	$this->installation->getPackageID(),
	'mail'
]);
