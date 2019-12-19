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
		throw new SystemException("Die Aktualisierung erfordert WoltLab Suite Core (com.woltlab.wcf) in Version 3.1.11 oder h&ouml;her.");
	}
	else {
		throw new SystemException("The update requires WoltLab Suite Core (com.woltlab.wcf) in version 3.1.11 or newer.");
	}
}

$requiredPHPVersion = '7.0.22';
$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', phpversion());
if (version_compare($comparePhpVersion, $requiredPHPVersion) === -1) {
	if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
		throw new SystemException("Die Aktualisierung erfordert PHP in Version {$requiredPHPVersion} oder h&ouml;her.");
	}
	else {
		throw new SystemException("The update requires PHP in version {$requiredPHPVersion} or newer.");
	}
}
