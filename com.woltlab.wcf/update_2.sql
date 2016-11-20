DROP TABLE IF EXISTS wcf1_media;
CREATE TABLE wcf1_media (
	mediaID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	
	filename VARCHAR(255) NOT NULL DEFAULT '',
	filesize INT(10) NOT NULL DEFAULT 0,
	fileType VARCHAR(255) NOT NULL DEFAULT '',
	fileHash VARCHAR(255) NOT NULL DEFAULT '',
	uploadTime INT(10) NOT NULL DEFAULT 0,
	userID INT(10),
	username VARCHAR(255) NOT NULL,
	languageID INT(10),
	isMultilingual TINYINT(1) NOT NULL DEFAULT 0,
	
	isImage TINYINT(1) NOT NULL DEFAULT 0,
	width SMALLINT(5) NOT NULL DEFAULT 0,
	height SMALLINT(5) NOT NULL DEFAULT 0,
	
	tinyThumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	tinyThumbnailSize INT(10) NOT NULL DEFAULT 0,
	tinyThumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	tinyThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	smallThumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	smallThumbnailSize INT(10) NOT NULL DEFAULT 0,
	smallThumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	smallThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	mediumThumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	mediumThumbnailSize INT(10) NOT NULL DEFAULT 0,
	mediumThumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	mediumThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	largeThumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	largeThumbnailSize INT(10) NOT NULL DEFAULT 0,
	largeThumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	largeThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_media_content;
CREATE TABLE wcf1_media_content (
	mediaID INT(10) NOT NULL,
	languageID INT(10),
	title VARCHAR(255) NOT NULL,
	caption TEXT,
	altText VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY (mediaID, languageID)
);

DROP TABLE IF EXISTS wcf1_menu;
CREATE TABLE wcf1_menu (
	menuID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	identifier VARCHAR(255) NOT NULL,
	title VARCHAR(255) NOT NULL,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	packageID INT(10) NOT NULL
);

DROP TABLE IF EXISTS wcf1_menu_item;
CREATE TABLE wcf1_menu_item (
	itemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	menuID INT(10) NOT NULL,
	parentItemID INT(10),
	identifier VARCHAR(255) NOT NULL,
	title VARCHAR(255) NOT NULL,
	pageID INT(10),
	pageObjectID INT(10) NOT NULL DEFAULT 0,
	externalURL VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	packageID INT(10) NOT NULL
);

ALTER TABLE wcf1_modification_log ADD parentObjectID INT(10);

ALTER TABLE wcf1_package_update_version DROP COLUMN isCritical;

DROP TABLE IF EXISTS wcf1_page_menu_item;
