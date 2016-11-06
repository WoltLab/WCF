DROP TABLE IF EXISTS wcf1_page;
CREATE TABLE wcf1_page (
	pageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parentPageID INT(10),
	identifier VARCHAR(255) NOT NULL,
	name VARCHAR(255) NOT NULL,
	pageType VARCHAR(255) NOT NULL,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	isLandingPage TINYINT(1) NOT NULL DEFAULT 0,
	isMultilingual TINYINT(1) NOT NULL DEFAULT 0,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	packageID INT(10) NOT NULL,
	applicationPackageID INT(10),
	controller VARCHAR(255) NOT NULL DEFAULT '',
	handler VARCHAR(255) NOT NULL DEFAULT '',
	controllerCustomURL VARCHAR(255) NOT NULL DEFAULT '',
	requireObjectID TINYINT(1) NOT NULL DEFAULT 0,
	hasFixedParent TINYINT(1) NOT NULL DEFAULT 0,
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	permissions TEXT NULL,
	options TEXT NULL
);

DROP TABLE IF EXISTS wcf1_page_content;
CREATE TABLE wcf1_page_content (
	pageContentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	pageID INT(10) NOT NULL,
	languageID INT(10),
	title VARCHAR(255) NOT NULL,
	content MEDIUMTEXT,
	metaDescription TEXT,
	metaKeywords TEXT,
	customURL VARCHAR(255) NOT NULL,
	hasEmbeddedObjects TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (pageID, languageID)
);

UPDATE wcf1_user_group_option SET optionName = 'admin.configuration.package.canInstallPackage', categoryName = 'admin.configuration.package' WHERE optionName = 'admin.system.package.canInstallPackage';