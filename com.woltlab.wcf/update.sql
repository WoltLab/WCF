-- reduce column length to support utf8mb4_unicode_ci
UPDATE wcf1_acl_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_acl_option CHANGE optionName optionName VARCHAR(191) NOT NULL;
UPDATE wcf1_acl_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_acl_option CHANGE categoryName	categoryName VARCHAR(191) NOT NULL;
UPDATE wcf1_acl_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_acl_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL;
UPDATE wcf1_acp_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_acp_menu_item CHANGE menuItem menuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_menu_item SET parentMenuItem = SUBSTRING(parentMenuItem, 1, 191);
ALTER TABLE wcf1_acp_menu_item CHANGE parentMenuItem parentMenuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_search_provider SET providerName = SUBSTRING(providerName, 1, 191);
ALTER TABLE wcf1_acp_search_provider CHANGE providerName providerName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_template SET templateName = SUBSTRING(templateName, 1, 191);
ALTER TABLE wcf1_acp_template CHANGE templateName templateName VARCHAR(191) NOT NULL;
UPDATE wcf1_bbcode SET bbcodeTag = SUBSTRING(bbcodeTag, 1, 191);
ALTER TABLE wcf1_bbcode CHANGE bbcodeTag bbcodeTag VARCHAR(191) NOT NULL;
UPDATE wcf1_clipboard_action SET actionClassName = SUBSTRING(actionClassName, 1, 191);
ALTER TABLE wcf1_clipboard_action CHANGE actionClassName actionClassName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_core_object SET objectName = SUBSTRING(objectName, 1, 191);
ALTER TABLE wcf1_core_object CHANGE objectName objectName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_import_mapping SET oldID = SUBSTRING(oldID, 1, 191);
ALTER TABLE wcf1_import_mapping CHANGE oldID oldID VARCHAR(191) NOT NULL;
UPDATE wcf1_language_category SET languageCategory = SUBSTRING(languageCategory, 1, 191);
ALTER TABLE wcf1_language_category CHANGE languageCategory languageCategory VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_language_item SET languageItem = SUBSTRING(languageItem, 1, 191);
ALTER TABLE wcf1_language_item CHANGE languageItem languageItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_object_type SET objectType = SUBSTRING(objectType, 1, 191);
ALTER TABLE wcf1_object_type CHANGE objectType objectType VARCHAR(191) NOT NULL;
UPDATE wcf1_object_type_definition SET definitionName = SUBSTRING(definitionName, 1, 191);
ALTER TABLE wcf1_object_type_definition CHANGE definitionName definitionName VARCHAR(191) NOT NULL;
UPDATE wcf1_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_exclusion SET excludedPackage = SUBSTRING(excludedPackage, 1, 191);
ALTER TABLE wcf1_package_exclusion CHANGE excludedPackage excludedPackage VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_installation_file_log SET filename = SUBSTRING(filename, 1, 191);
ALTER TABLE wcf1_package_installation_file_log CHANGE filename filename VARBINARY(765) NOT NULL;
UPDATE wcf1_package_installation_plugin SET pluginName = SUBSTRING(pluginName, 1, 191);
ALTER TABLE wcf1_package_installation_plugin CHANGE pluginName pluginName VARCHAR(191) NOT NULL;
UPDATE wcf1_package_update SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package_update CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_update_exclusion SET excludedPackage = SUBSTRING(excludedPackage, 1, 191);
ALTER TABLE wcf1_package_update_exclusion CHANGE excludedPackage excludedPackage VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_update_requirement SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package_update_requirement CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_search_keyword SET keyword = SUBSTRING(keyword, 1, 191);
ALTER TABLE wcf1_search_keyword CHANGE keyword keyword VARCHAR(191) NOT NULL;
UPDATE wcf1_session SET userAgent = SUBSTRING(userAgent, 1, 191);
ALTER TABLE wcf1_session CHANGE userAgent userAgent VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_session_virtual SET userAgent = SUBSTRING(userAgent, 1, 191);
ALTER TABLE wcf1_session_virtual CHANGE userAgent userAgent VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_smiley SET smileyCode = SUBSTRING(smileyCode, 1, 191);
ALTER TABLE wcf1_smiley CHANGE smileyCode smileyCode VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_spider SET spiderIdentifier = SUBSTRING(spiderIdentifier, 1, 191);
ALTER TABLE wcf1_spider CHANGE spiderIdentifier spiderIdentifier VARCHAR(191) DEFAULT '';
UPDATE wcf1_tag SET name = SUBSTRING(name, 1, 191);
ALTER TABLE wcf1_tag CHANGE name name VARCHAR(191) NOT NULL;
UPDATE wcf1_template SET templateName = SUBSTRING(templateName, 1, 191);
ALTER TABLE wcf1_template CHANGE templateName templateName VARCHAR(191) NOT NULL;
UPDATE wcf1_user SET username = SUBSTRING(username, 1, 100);
ALTER TABLE wcf1_user CHANGE username username VARCHAR(100) NOT NULL DEFAULT '';
UPDATE wcf1_user SET email = SUBSTRING(email, 1, 191);
ALTER TABLE wcf1_user CHANGE email email VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user SET authData = SUBSTRING(authData, 1, 191);
ALTER TABLE wcf1_user CHANGE authData authData VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_collapsible_content SET objectID = SUBSTRING(objectID, 1, 191);
ALTER TABLE wcf1_user_collapsible_content CHANGE objectID objectID VARCHAR(191) NOT NULL;
UPDATE wcf1_user_group_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_user_group_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_group_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_group_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_user_group_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_user_menu_item CHANGE menuItem menuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_menu_item SET parentMenuItem = SUBSTRING(parentMenuItem, 1, 191);
ALTER TABLE wcf1_user_menu_item CHANGE parentMenuItem parentMenuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_notification_event SET eventName = SUBSTRING(eventName, 1, 191);
ALTER TABLE wcf1_user_notification_event CHANGE eventName eventName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_user_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_user_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_profile_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_user_profile_menu_item CHANGE menuItem	menuItem VARCHAR(191) NOT NULL;

-- other changes
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

ALTER TABLE wcf1_session DROP COLUMN controller;
ALTER TABLE wcf1_session DROP COLUMN parentObjectType;
ALTER TABLE wcf1_session DROP COLUMN parentObjectID;
ALTER TABLE wcf1_session DROP COLUMN objectType;
ALTER TABLE wcf1_session DROP COLUMN objectID;
ALTER TABLE wcf1_session DROP COLUMN sessionVariables;

ALTER TABLE wcf1_session ADD pageID INT(10);
ALTER TABLE wcf1_session ADD pageObjectID INT(10);
ALTER TABLE wcf1_session ADD parentPageID INT(10);
ALTER TABLE wcf1_session ADD parentPageObjectID INT(10);
ALTER TABLE wcf1_session ADD KEY pageID (pageID, pageObjectID);
ALTER TABLE wcf1_session ADD KEY parentPageID (parentPageID, parentPageObjectID);

ALTER TABLE wcf1_session_virtual ADD sessionVariables MEDIUMTEXT;

DROP TABLE IF EXISTS wcf1_sitemap;

ALTER TABLE wcf1_smiley ADD smileyPath2x VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE wcf1_style ADD packageName VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE wcf1_style ADD isTainted TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_template_listener ADD permissions TEXT;
ALTER TABLE wcf1_template_listener ADD options TEXT;

ALTER TABLE wcf1_user CHANGE lostPasswordKey lostPasswordKey CHAR(40) DEFAULT NULL;
ALTER TABLE wcf1_user DROP COLUMN signatureEnableBBCodes;
ALTER TABLE wcf1_user DROP COLUMN signatureEnableSmilies;
ALTER TABLE wcf1_user DROP COLUMN socialNetworkPrivacySettings;

ALTER TABLE wcf1_user_avatar DROP COLUMN cropX;
ALTER TABLE wcf1_user_avatar DROP COLUMN cropY;

ALTER TABLE wcf1_user_notification ADD KEY (confirmTime);

DELETE FROM wcf1_user_profile_visitor WHERE userID IS NULL OR ownerID IS NULL;
ALTER TABLE wcf1_user_profile_visitor CHANGE ownerID ownerID INT(10) NOT NULL;
ALTER TABLE wcf1_user_profile_visitor CHANGE userID userID INT(10) NOT NULL;

ALTER TABLE wcf1_user_storage ADD KEY (field);

/* foreign keys */
ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_virtual ADD FOREIGN KEY (sessionID) REFERENCES wcf1_acp_session (sessionID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wcf1_article ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_article ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE wcf1_article_content ADD FOREIGN KEY (articleID) REFERENCES wcf1_article (articleID) ON DELETE CASCADE;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_box ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (linkPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

ALTER TABLE wcf1_box_content ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;

-- re-add dropped foreign key
ALTER TABLE wcf1_event_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_media ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_media ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_media_content ADD FOREIGN KEY (mediaID) REFERENCES wcf1_media (mediaID) ON DELETE CASCADE;
ALTER TABLE wcf1_media_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (parentItemID) REFERENCES wcf1_menu_item (itemID) ON DELETE SET NULL;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_page ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_page ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page ADD FOREIGN KEY (applicationPackageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_page_content ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_session ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_session ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

-- remove obsolete update servers
DELETE FROM wcf1_package_update_server WHERE serverURL IN ('http://update.woltlab.com/maelstrom/', 'http://store.woltlab.com/maelstrom/', 'http://update.woltlab.com/typhoon/', 'http://store.woltlab.com/typhoon/');

-- style default values
DELETE FROM wcf1_style;
DELETE FROM wcf1_style_variable;
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('individualScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('messageSidebarOrientation', 'left');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('overrideScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogo', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoWidth', '281');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoHeight', '40');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoMobile', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useFluidLayout', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useGoogleFont', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBackground', 'rgba(207, 216, 220, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBackgroundActive', 'rgba(120, 144, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonDisabledBackground', 'rgba(223, 223, 223, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonDisabledText', 'rgba(165, 165, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBackground', 'rgba(33, 150, 243, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBackgroundActive', 'rgba(26, 119, 201, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonText', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBackground', 'rgba(250, 250, 250, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBorder', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBorderInner', 'rgba(224, 224, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedLink', 'rgba(52, 73, 94, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedLinkActive', 'rgba(52, 73, 94, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedText', 'rgba(125, 130, 135, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineBorder', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackground', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackgroundActive', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBorderInner', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownLink', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownLinkActive', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownText', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontFamilyFallback', '"Segoe UI", "DejaVu Sans", "Lucida Grande", "Helvetica", sans-serif');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontFamilyGoogle', 'Open Sans');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontLineHeight', '1.48');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeDefault', '14px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeHeadline', '18px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeSection', '23px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeSmall', '12px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeTitle', '28px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxBackground', 'rgba(236, 239, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightLink', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightText', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineText', 'rgba(189, 195, 199, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterText', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLink', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackground', 'rgba(43, 79, 113, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackground', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBorder', 'rgba(55, 73, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxBackgroundActive', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxPlaceholder', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxPlaceholderActive', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBackground', 'rgba(241, 246, 251, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBackgroundActive', 'rgba(241, 246, 251, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorder', 'rgba(176, 200, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderActive', 'rgba(41, 128, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledBackground', 'rgba(245, 245, 245, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledBorder', 'rgba(174, 176, 179, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledText', 'rgba(125, 130, 100, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputLabel', 'rgba(59, 109, 169, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputTextActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputPlaceholder', 'rgba(169, 169, 169, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputPlaceholderActive', 'rgba(204, 204, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutFixedWidth', '1200px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMaxWidth', '1400px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMinWidth', '1240px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationBackground', 'rgba(236, 239, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationText', 'rgba(170, 170, 170, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarBackground', 'rgba(236, 241, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedText', 'rgba(127, 140, 141, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorBackground', 'rgba(242, 222, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorBorder', 'rgba(235, 204, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLink', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLinkActive', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorText', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBackground', 'rgba(217, 237, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBorder', 'rgba(188, 223, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLink', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLinkActive', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoText', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBackground', 'rgba(223, 240, 216, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBorder', 'rgba(208, 233, 198, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLink', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLinkActive', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessText', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBackground', 'rgba(252, 248, 227, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBorder', 'rgba(250, 242, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLink', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLinkActive', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningText', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxBackgroundActive', 'rgba(242, 242, 242, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxBorderInner', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxHeadline', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxHeadlineActive', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowDark', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowLight', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipBackground', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipText', 'rgba(255, 255, 255, 1)');

-- Email template group
INSERT INTO wcf1_template_group (parentTemplateGroupID, templateGroupName, templateGroupFolderName) VALUES (NULL, 'wcf.acp.template.group.email', '_wcf_email/');

-- media providers
INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouTube Playlist', 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/)playlist\\?(?:.*?&)?list=(?P<ID>[a-zA-Z0-9_-]+)', '<div class="videoContainer"><iframe src="https://www.youtube.com/embed/videoseries?list={$ID}" allowfullscreen></iframe></div>');
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://vimeo\\.com/(?:channels/[^/]+/)?(?P<ID>\\d+)' WHERE title = 'Vimeo';
