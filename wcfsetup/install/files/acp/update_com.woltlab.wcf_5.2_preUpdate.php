<?php
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
if (Package::compareVersion(WCF_VERSION, '3.1.11', '<')) {
	if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
		throw new SystemException("Die Aktualisierung erfordert WoltLab Suite Core (com.woltlab.wcf) in Version 3.1.11 oder höher.");
	}
	else {
		throw new SystemException("The update requires WoltLab Suite Core (com.woltlab.wcf) in version 3.1.11 or newer.");
	}
}

$requiredPHPVersion = '7.0.22';
$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', phpversion());
if (version_compare($comparePhpVersion, $requiredPHPVersion) === -1) {
	if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
		throw new SystemException("Die Aktualisierung erfordert PHP in Version {$requiredPHPVersion} oder höher.");
	}
	else {
		throw new SystemException("The update requires PHP in version {$requiredPHPVersion} or newer.");
	}
}

// check sql version
$sqlVersion = WCF::getDB()->getVersion();
$compareSQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
if (stripos($sqlVersion, 'MariaDB') === false) {
	// For MySQL 8.0, MySQL 8.0.14+ is required
	// https://bugs.mysql.com/bug.php?id=88718
	if ($compareSQLVersion[0] === '8') {
		// MySQL 8.0.14+
		if (!(version_compare($compareSQLVersion, '8.0.14') >= 0)) {
			if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
				throw new SystemException("Ihre eingesetzte Version von MySQL 8 enthält einen bekannten Fehler und verhindert eine Aktualisierung, es wird mindestens MySQL 8.0.14 oder höher benötigt.");
			}
			else {
				throw new SystemException("The version of MySQL 8 that you are using contains a known bug that prevents an upgrade, MySQL 8.0.14 or newer is required.");
			}
		}
	}
}
