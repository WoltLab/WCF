<?php

use wcf\system\WCF;

/**
 * Removes the files belonging to the old MailForm in order to make this completely non-functional.
 * 
 * @author	Tim Duesterhus, Florian Gail
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

if (file_exists(WCF_DIR . 'lib/form/MailForm.class.php')) {
	unlink(WCF_DIR . 'lib/form/MailForm.class.php');
}

// At this point the MailForm.class.php must be absent. Either the unlink() succeeded or
// the file already was gone.
$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
	WHERE		packageID = ?
			AND filename = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	$this->installation->getPackageID(),
	'lib/form/MailForm.class.php'
]);

if (file_exists(WCF_DIR . 'templates/mail.tpl')) {
	unlink(WCF_DIR . 'templates/mail.tpl');
}

$sql = "DELETE FROM	wcf".WCF_N."_template
	WHERE		packageID = ?
			AND templateName = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	$this->installation->getPackageID(),
	'mail'
]);
