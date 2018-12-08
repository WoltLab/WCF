<?php
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
if (Package::compareVersion(WCF_VERSION, '3.0.11', '<')) {
	if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
		throw new SystemException("Die Aktualisierung erfordert WoltLab Suite Core (com.woltlab.wcf) in Version 3.0.11 oder h&ouml;her.");
	}
	else {
		throw new SystemException("The update requires WoltLab Suite Core (com.woltlab.wcf) in version 3.0.11 or later.");
	}
}
