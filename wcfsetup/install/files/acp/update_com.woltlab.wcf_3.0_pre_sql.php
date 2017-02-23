<?php
use wcf\system\exception\SystemException;
use wcf\system\WCF;

$phpVersion = phpversion();
$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $phpVersion);
$neededPhpVersion = '5.5.4';
if (!(version_compare($comparePhpVersion, $neededPhpVersion) >= 0)) {
	$message = "Your PHP version '{$phpVersion}' is insufficient for installation of this software. PHP version {$neededPhpVersion} or greater is required.";
	if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
		$message = "Ihre PHP Version '{$phpVersion}' ist unzureichend f&uuml;r die Installation dieser Software. PHP Version {$neededPhpVersion} oder h&ouml;her wird ben&ouml;tigt.";
	}
	
	throw new SystemException($message);
}

// change encoding of wcf1_acp_session; necessary for foreign key in wcf1_acp_session_virtual
$sql = "ALTER TABLE wcf".WCF_N."_acp_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// create wcf1_acp_session_virtual
$sql = "DROP TABLE IF EXISTS wcf".WCF_N."_acp_session_virtual";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
$sql = "CREATE TABLE wcf".WCF_N."_acp_session_virtual (
	virtualSessionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID CHAR(40) NOT NULL,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(191) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0
	UNIQUE KEY (sessionID, ipAddress, userAgent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// create virtual session for current user
$sql = "INSERT INTO wcf".WCF_N."_acp_session_virtual (sessionID, ipAddress, userAgent, lastActivityTime) SELECT sessionID, ipAddress, userAgent, lastActivityTime FROM wcf".WCF_N."_acp_session WHERE sessionID = ?";
$statement = \wcf\system\WCF::getDB()->prepareStatement($sql);
// WARNING: do not use [...] array syntax here, as this file is also used to check for PHP 5.5+
$statement->execute(array(WCF::getSession()->sessionID));

// create session cookie
@header('Set-Cookie: '.rawurlencode(COOKIE_PREFIX . 'cookieHash_acp').'='.rawurlencode(WCF::getSession()->sessionID).'; path=/'.(\wcf\system\request\RouteHandler::secureConnection() ? '; secure' : '').'; HttpOnly', false);
