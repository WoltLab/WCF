-- remove default media providers (they'll be re-added later during the upgrade)
DELETE FROM wcf1_bbcode_media_provider WHERE title IN ('YouTube', 'YouTube Playlist', 'Vimeo', 'Clipfish', 'Veoh', 'DailyMotion', 'github gist', 'Soundcloud', 'Soundcloud set');
UPDATE wcf1_bbcode_media_provider SET name = CONCAT('com.woltlab.wcf.generic', providerID), packageID = 1;
ALTER TABLE wcf1_bbcode_media_provider ADD UNIQUE KEY name (name, packageID);

DROP TABLE IF EXISTS wcf1_contact_option;
CREATE TABLE wcf1_contact_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	optionTitle VARCHAR(255) NOT NULL DEFAULT '',
	optionDescription TEXT,
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue MEDIUMTEXT,
	validationPattern TEXT,
	selectOptions MEDIUMTEXT,
	required TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_contact_recipient;
CREATE TABLE wcf1_contact_recipient (
	recipientID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	isAdministrator TINYINT(1) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_devtools_project;
CREATE TABLE wcf1_devtools_project (
	projectID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(191) NOT NULL,
	path TEXT,
	
	UNIQUE KEY name (name)
);

ALTER TABLE wcf1_event_listener CHANGE eventClassName eventClassName VARCHAR(255) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS wcf1_package_compatibility;
CREATE TABLE wcf1_package_compatibility (
	packageID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageID, version)
);

DROP TABLE IF EXISTS wcf1_package_update_compatibility;
CREATE TABLE wcf1_package_update_compatibility (
	packageUpdateVersionID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageUpdateVersionID, version)
);

ALTER TABLE wcf1_package_update_server CHANGE COLUMN apiVersion apiVersion ENUM('2.0', '2.1', '3.1') NOT NULL DEFAULT '2.0';

DROP TABLE IF EXISTS wcf1_page_box_order;
CREATE TABLE wcf1_page_box_order (
	pageID INT(10) NOT NULL,
	boxID INT(10) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY pageToBox (pageID, boxID)
);

DROP TABLE IF EXISTS wcf1_registry;
CREATE TABLE wcf1_registry (
	packageID INT(10) NOT NULL,
	field VARCHAR(191) NOT NULL,
	fieldValue MEDIUMTEXT,
	
	UNIQUE KEY uniqueField (packageID, field)
);

DROP TABLE IF EXISTS wcf1_trophy;
CREATE TABLE wcf1_trophy(
	trophyID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(255),
	description MEDIUMTEXT,	
	categoryID INT(10) NOT NULL,
	type SMALLINT(1) DEFAULT 1,
	iconFile MEDIUMTEXT, 
	iconName VARCHAR(255),
	iconColor VARCHAR(255),
	badgeColor VARCHAR(255),
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	awardAutomatically TINYINT(1) NOT NULL DEFAULT 0,
	KEY(categoryID)
);

DROP TABLE IF EXISTS wcf1_user_special_trophy;
CREATE TABLE wcf1_user_special_trophy(
	trophyID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY (trophyID, userID)
);

DROP TABLE IF EXISTS wcf1_user_trophy;
CREATE TABLE wcf1_user_trophy(
	userTrophyID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	trophyID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	description MEDIUMTEXT,
	useCustomDescription TINYINT(1) NOT NULL DEFAULT 0,
	KEY(trophyID, time)
);

ALTER TABLE wcf1_article_content ADD FOREIGN KEY (teaserImageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;
ALTER TABLE wcf1_bbcode_media_provider ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_media ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE wcf1_package_compatibility ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_update_compatibility ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_box_order ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_box_order ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_registry ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_trophy ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_trophy ADD FOREIGN KEY (trophyID) REFERENCES wcf1_trophy (trophyID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_trophy ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_special_trophy ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_special_trophy ADD FOREIGN KEY (trophyID) REFERENCES wcf1_trophy (trophyID) ON DELETE CASCADE;

-- update servers are added through a script to avoid collisions

INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBackground', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBorder', 'rgba(236, 241, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonBackgroundActive', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonTextDisabled', 'rgba(165, 165, 165, 1)');

-- default options: subject and message
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, required, showOrder, originIsSystem) VALUES (1, 'wcf.contact.option1', 'wcf.contact.optionDescription1', 'text', 1, 1, 1);
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, required, showOrder, originIsSystem) VALUES (2, 'wcf.contact.option2', '', 'textarea', 1, 1, 1);

-- default recipient: site administrator
INSERT INTO wcf1_contact_recipient (recipientID, name, email, isAdministrator, originIsSystem) VALUES (1, 'wcf.contact.recipient.name1', '', 1, 1);

-- Force-enable the visibility of *all* pages by setting `allowSpidersToIndex` to `2`.
-- 
-- This value isn't valid by definition, but because it is considered to be a true-ish
-- value, we can use this to imply an "implicit yes" without breaking any checks. Check
-- the PagePackageInstallationPlugin to see what this magic value is good for.
UPDATE wcf1_page SET allowSpidersToIndex = 1 WHERE pageType <> 'system';
UPDATE wcf1_page SET allowSpidersToIndex = 2 WHERE pageType = 'system';
