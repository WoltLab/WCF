DROP TABLE IF EXISTS wcf1_acl_simple_to_user;
CREATE TABLE wcf1_acl_simple_to_user (
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY userKey (objectTypeID, objectID, userID)
);

DROP TABLE IF EXISTS wcf1_acl_simple_to_group;
CREATE TABLE wcf1_acl_simple_to_group (
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	groupID INT(10) NOT NULL,
	UNIQUE KEY groupKey (objectTypeID, objectID, groupID)
);

ALTER TABLE wcf1_acp_menu_item ADD icon VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE wcf1_acp_session DROP COLUMN controller;
ALTER TABLE wcf1_acp_session DROP COLUMN parentObjectType;
ALTER TABLE wcf1_acp_session DROP COLUMN parentObjectID;
ALTER TABLE wcf1_acp_session DROP COLUMN objectType;
ALTER TABLE wcf1_acp_session DROP COLUMN objectID;
ALTER TABLE wcf1_acp_session DROP COLUMN sessionVariables;

ALTER TABLE wcf1_application DROP COLUMN cookiePath;
ALTER TABLE wcf1_application DROP COLUMN isPrimary;
ALTER TABLE wcf1_application ADD isTainted TINYINT(1) NOT NULL DEFAULT 0;

DROP TABLE IF EXISTS wcf1_article;
CREATE TABLE wcf1_article (
	articleID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	categoryID INT(10),
	isMultilingual TINYINT(1) NOT NULL DEFAULT 0,
	publicationStatus TINYINT(1) NOT NULL DEFAULT 1,
	publicationDate INT(10) NOT NULL DEFAULT 0,
	enableComments TINYINT(1) NOT NULL DEFAULT 1,
	comments SMALLINT(5) NOT NULL DEFAULT 0,
	views MEDIUMINT(7) NOT NULL DEFAULT 0,
	cumulativeLikes MEDIUMINT(7) NOT NULL DEFAULT 0,
	
	KEY (time)
);

DROP TABLE IF EXISTS wcf1_article_content;
CREATE TABLE wcf1_article_content (
	articleContentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	articleID INT(10) NOT NULL,
	languageID INT(10),
	title VARCHAR(255) NOT NULL,
	teaser TEXT,
	content MEDIUMTEXT,
	imageID INT(10),
	hasEmbeddedObjects TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (articleID, languageID)
);

DROP TABLE IF EXISTS wcf1_background_job;
CREATE TABLE wcf1_background_job (
	jobID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	job MEDIUMBLOB NOT NULL,
	status ENUM('ready', 'processing') NOT NULL DEFAULT 'ready',
	time INT(10) NOT NULL,
	KEY (status, time)
);

ALTER TABLE wcf1_bbcode DROP COLUMN allowedChildren;
ALTER TABLE wcf1_bbcode DROP COLUMN isDisabled;
ALTER TABLE wcf1_bbcode ADD isBlockElement TINYINT(1) NOT NULL DEFAULT 0;

DROP TABLE IF EXISTS wcf1_box;
CREATE TABLE wcf1_box (
	boxID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10),
	identifier VARCHAR(255) NOT NULL,
	name VARCHAR(255) NOT NULL,
	boxType VARCHAR(255) NOT NULL,
	position VARCHAR(255) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	visibleEverywhere TINYINT(1) NOT NULL DEFAULT 1,
	isMultilingual TINYINT(1) NOT NULL DEFAULT 0,
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	showHeader TINYINT(1) NOT NULL DEFAULT 1,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	packageID INT(10) NOT NULL,
	menuID INT(10) NULL,
	linkPageID INT(10),
	linkPageObjectID INT(10) NOT NULL DEFAULT 0,
	externalURL VARCHAR(255) NOT NULL DEFAULT '',
	additionalData TEXT
);

DROP TABLE IF EXISTS wcf1_box_content;
CREATE TABLE wcf1_box_content (
	boxContentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	boxID INT(10) NOT NULL,
	languageID INT(10),
	title VARCHAR(255) NOT NULL,
	content MEDIUMTEXT,
	imageID INT(10),
	hasEmbeddedObjects TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (boxID, languageID)
);

DROP TABLE IF EXISTS wcf1_box_to_page;
CREATE TABLE wcf1_box_to_page (
	boxID INT(10) NOT NULL,
	pageID INT(10) NOT NULL,
	visible TINYINT(1) NOT NULL DEFAULT 1,
	
	UNIQUE KEY (pageID, boxID),
	KEY (pageID, visible)
);

ALTER TABLE wcf1_clipboard_item ADD KEY (userID);

ALTER TABLE wcf1_cronjob ADD cronjobName VARCHAR(191) NOT NULL;
ALTER TABLE wcf1_cronjob ADD options TEXT;
UPDATE wcf1_cronjob SET cronjobName = CONCAT('com.woltlab.wcf.generic', cronjobID);
ALTER TABLE wcf1_cronjob ADD UNIQUE KEY cronjobName (cronjobName, packageID);

DROP TABLE IF EXISTS wcf1_dashboard_option;
DROP TABLE IF EXISTS wcf1_dashboard_box;

-- we have to drop the foreign key first to drop the normal key
ALTER TABLE wcf1_event_listener DROP FOREIGN KEY packageID;
ALTER TABLE wcf1_event_listener DROP KEY packageID;
ALTER TABLE wcf1_event_listener ADD listenerName VARCHAR(191) NOT NULL;
ALTER TABLE wcf1_event_listener CHANGE eventName eventName TEXT;
ALTER TABLE wcf1_event_listener ADD permissions TEXT;
ALTER TABLE wcf1_event_listener ADD options TEXT;

UPDATE wcf1_event_listener SET listenerName = CONCAT('com.woltlab.wcf.generic', listenerID);
ALTER TABLE wcf1_event_listener ADD UNIQUE KEY listenerName (listenerName, packageID);

ALTER TABLE wcf1_label ADD showOrder INT(10) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_language ADD isDisabled TINYINT(1) NOT NULL DEFAULT 0;

DROP TABLE IF EXISTS wcf1_language_server;
