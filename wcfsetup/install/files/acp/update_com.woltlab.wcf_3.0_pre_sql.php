<?php
use wcf\system\WCF;

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
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	sessionVariables MEDIUMTEXT,
	UNIQUE KEY (sessionID, ipAddress, userAgent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// create virtual session for current user
$sql = "INSERT INTO wcf".WCF_N."_acp_session_virtual (sessionID, ipAddress, userAgent, lastActivityTime, sessionVariables) SELECT sessionID, ipAddress, userAgent, lastActivityTime, sessionVariables FROM wcf".WCF_N."_acp_session WHERE sessionID = ?";
$statement = \wcf\system\WCF::getDB()->prepareStatement($sql);
$statement->execute([WCF::getSession()->sessionID]);

// create session cookie
@header('Set-Cookie: '.rawurlencode(COOKIE_PREFIX . 'cookieHash_acp').'='.rawurlencode(WCF::getSession()->sessionID).'; path=/'.(\wcf\system\request\RouteHandler::secureConnection() ? '; secure' : '').'; HttpOnly', false);
