/* tables */
DROP TABLE IF EXISTS wcf1_acl_option;
CREATE TABLE wcf1_acl_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	optionName VARCHAR(255) NOT NULL,
	categoryName VARCHAR(255) NOT NULL,
	UNIQUE KEY (packageID, objectTypeID, optionName)
);

DROP TABLE IF EXISTS wcf1_acl_option_category;
CREATE TABLE wcf1_acl_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	categoryName VARCHAR(255) NOT NULL,
	UNIQUE KEY (packageID, objectTypeID, categoryName)
);

DROP TABLE IF EXISTS wcf1_acl_option_to_user;
CREATE TABLE wcf1_acl_option_to_user (
	optionID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	optionValue TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY userID (userID, objectID, optionID)
);

DROP TABLE IF EXISTS wcf1_acl_option_to_group;
CREATE TABLE wcf1_acl_option_to_group (
	optionID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	groupID INT(10) NOT NULL,
	optionValue TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY groupID (groupID, objectID, optionID)
);

DROP TABLE IF EXISTS wcf1_acp_menu_item;
CREATE TABLE wcf1_acp_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(255) NOT NULL DEFAULT '',
	menuItemController VARCHAR(255) NOT NULL DEFAULT '',
	menuItemLink VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY menuItem (menuItem, packageID)
);

DROP TABLE IF EXISTS wcf1_acp_search_provider;
CREATE TABLE wcf1_acp_search_provider (
	providerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	providerName VARCHAR(255) NOT NULL DEFAULT '',
	className VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY providerName (providerName, packageID)
);

DROP TABLE IF EXISTS wcf1_acp_session;
CREATE TABLE wcf1_acp_session (
	sessionID CHAR(40) NOT NULL PRIMARY KEY,
	userID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(7) NOT NULL DEFAULT '',
	controller VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectType VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectID INT(10) NOT NULL DEFAULT 0,
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	objectID INT(10) NOT NULL DEFAULT 0,
	sessionVariables MEDIUMTEXT
);

DROP TABLE IF EXISTS wcf1_acp_session_access_log;
CREATE TABLE wcf1_acp_session_access_log (
	sessionAccessLogID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionLogID INT(10) NOT NULL,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(7) NOT NULL DEFAULT '',
	className VARCHAR(255) NOT NULL DEFAULT '',
	KEY sessionLogID (sessionLogID)
);

DROP TABLE IF EXISTS wcf1_acp_session_log;
CREATE TABLE wcf1_acp_session_log (
	sessionLogID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID CHAR(40) NOT NULL DEFAULT '',
	userID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	hostname VARCHAR(255) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	KEY sessionID (sessionID)
);

DROP TABLE IF EXISTS wcf1_acp_template;
CREATE TABLE wcf1_acp_template (
	templateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	templateName VARCHAR(255) NOT NULL,
	application VARCHAR(20) NOT NULL,
	UNIQUE KEY applicationTemplate (application, templateName)
);

DROP TABLE IF EXISTS wcf1_application;
CREATE TABLE wcf1_application (
	packageID INT(10) NOT NULL PRIMARY KEY,
	domainName VARCHAR(255) NOT NULL,
	domainPath VARCHAR(255) NOT NULL DEFAULT '/',
	cookieDomain VARCHAR(255) NOT NULL,
	cookiePath VARCHAR(255) NOT NULL DEFAULT '/',
	isPrimary TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_attachment;
CREATE TABLE wcf1_attachment (
	attachmentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10),
	userID INT(10),
	tmpHash VARCHAR(40) NOT NULL DEFAULT '',
	filename VARCHAR(255) NOT NULL DEFAULT '',
	filesize INT(10) NOT NULL DEFAULT 0,
	fileType VARCHAR(255) NOT NULL DEFAULT '',
	fileHash VARCHAR(40) NOT NULL DEFAULT '',
	
	isImage TINYINT(1) NOT NULL DEFAULT 0,
	width SMALLINT(5) NOT NULL DEFAULT 0,
	height SMALLINT(5) NOT NULL DEFAULT 0, 
	
	tinyThumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	tinyThumbnailSize INT(10) NOT NULL DEFAULT 0,
	tinyThumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	tinyThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	thumbnailType VARCHAR(255) NOT NULL DEFAULT '',
	thumbnailSize INT(10) NOT NULL DEFAULT 0,
	thumbnailWidth SMALLINT(5) NOT NULL DEFAULT 0,
	thumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	downloads INT(10) NOT NULL DEFAULT 0,
	lastDownloadTime INT(10) NOT NULL DEFAULT 0,
	uploadTime INT(10) NOT NULL DEFAULT 0,
	showOrder SMALLINT(5) NOT NULL DEFAULT 0,
	KEY (objectTypeID, objectID),
	KEY (objectTypeID, tmpHash),
	KEY (objectID, uploadTime)
);

DROP TABLE IF EXISTS wcf1_bbcode;
CREATE TABLE wcf1_bbcode (
	bbcodeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	bbcodeTag VARCHAR(255) NOT NULL,
	packageID INT(10) NOT NULL,
	htmlOpen VARCHAR(255) NOT NULL DEFAULT '',
	htmlClose VARCHAR(255) NOT NULL DEFAULT '',
	allowedChildren VARCHAR(255) NOT NULL DEFAULT 'all',
	className VARCHAR(255) NOT NULL DEFAULT '',
	wysiwygIcon varchar(255) NOT NULL DEFAULT '',
	buttonLabel VARCHAR(255) NOT NULL DEFAULT '',
	isSourceCode TINYINT(1) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	showButton TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY bbcodeTag (bbcodeTag)
);

DROP TABLE IF EXISTS wcf1_bbcode_attribute;
CREATE TABLE wcf1_bbcode_attribute (
	attributeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	bbcodeID INT(10) NOT NULL,
	attributeNo TINYINT(3) NOT NULL DEFAULT 0,
	attributeHtml VARCHAR(255) NOT NULL DEFAULT '',
	validationPattern VARCHAR(255) NOT NULL DEFAULT '',
	required TINYINT(1) NOT NULL DEFAULT 0,
	useText TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY attributeNo (bbcodeID, attributeNo)
);

DROP TABLE IF EXISTS wcf1_bbcode_media_provider;
CREATE TABLE wcf1_bbcode_media_provider (
	providerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(255) NOT NULL,
	regex TEXT NOT NULL,
	html TEXT NOT NULL
);

DROP TABLE IF EXISTS wcf1_category;
CREATE TABLE wcf1_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	parentCategoryID INT(10) NOT NULL,
	title VARCHAR(255) NOT NULL,
	description TEXT,
	showOrder INT(10) NOT NULL,
	time INT(10) NOT NULL,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	additionalData TEXT
);

DROP TABLE IF EXISTS wcf1_clipboard_action;
CREATE TABLE wcf1_clipboard_action (
	actionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL DEFAULT 0,
	actionName VARCHAR(50) NOT NULL DEFAULT '',
	actionClassName VARCHAR(200) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY actionName (packageID, actionName, actionClassName)
);

DROP TABLE IF EXISTS wcf1_clipboard_item;
CREATE TABLE wcf1_clipboard_item (
	objectTypeID INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NOT NULL DEFAULT 0,
	objectID INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (objectTypeID, userID, objectID)
);

DROP TABLE IF EXISTS wcf1_clipboard_page;
CREATE TABLE wcf1_clipboard_page (
	pageClassName VARCHAR(80) NOT NULL DEFAULT '',
	packageID INT(10) NOT NULL DEFAULT 0,
	actionID INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_comment;
CREATE TABLE wcf1_comment (
	commentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	userID INT(10),
	username VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	responses MEDIUMINT(7) NOT NULL DEFAULT '0',
	lastResponseIDs VARCHAR(255) NOT NULL DEFAULT '',
	
	KEY (objectTypeID, objectID, time)
);

DROP TABLE IF EXISTS wcf1_comment_response;
CREATE TABLE wcf1_comment_response (
	responseID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	commentID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	userID INT(10),
	username VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	
	KEY (commentID, time)
);

DROP TABLE IF EXISTS wcf1_core_object;
CREATE TABLE wcf1_core_object (
	objectID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectName VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY object (packageID, objectName)
);

DROP TABLE IF EXISTS wcf1_cronjob;
CREATE TABLE wcf1_cronjob (
	cronjobID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	className varchar(255) NOT NULL DEFAULT '',
	packageID INT(10) NOT NULL,
	description varchar(255) NOT NULL DEFAULT '',
	startMinute varchar(255) NOT NULL DEFAULT '*',
	startHour varchar(255) NOT NULL DEFAULT '*',
	startDom varchar(255) NOT NULL DEFAULT '*',
	startMonth varchar(255) NOT NULL DEFAULT '*',
	startDow varchar(255) NOT NULL DEFAULT '*',
	lastExec INT(10) NOT NULL DEFAULT 0,
	nextExec INT(10) NOT NULL DEFAULT 0,
	afterNextExec INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	canBeEdited TINYINT(1) NOT NULL DEFAULT 1,
	canBeDisabled TINYINT(1) NOT NULL DEFAULT 1,
	state TINYINT(1) NOT NULL DEFAULT 0,
	failCount TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_cronjob_log;
CREATE TABLE wcf1_cronjob_log (
	cronjobLogID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	cronjobID INT(10) NOT NULL,
	execTime INT(10) NOT NULL DEFAULT 0,
	success TINYINT(1) NOT NULL DEFAULT 0,
	error TEXT
);

DROP TABLE IF EXISTS wcf1_dashboard_box;
CREATE TABLE wcf1_dashboard_box (
	boxID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	boxName VARCHAR(255) NOT NULL DEFAULT '',
	boxType VARCHAR(30) NOT NULL DEFAULT 'sidebar', -- can be 'content' or 'sidebar'
	className VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_dashboard_option;
CREATE TABLE wcf1_dashboard_option (
	objectTypeID INT(10) NOT NULL,
	boxID INT(10) NOT NULL,
	showOrder INT(10) NOT NULL,
	UNIQUE KEY dashboardOption (objectTypeID, boxID)
);

DROP TABLE IF EXISTS wcf1_event_listener;
CREATE TABLE wcf1_event_listener (
	listenerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	environment ENUM('user', 'admin') NOT NULL DEFAULT 'user',
	eventClassName VARCHAR(80) NOT NULL DEFAULT '',
	eventName VARCHAR(50) NOT NULL DEFAULT '',
	listenerClassName VARCHAR(200) NOT NULL DEFAULT '',
	inherit TINYINT(1) NOT NULL DEFAULT 0,
	niceValue TINYINT(3) NOT NULL DEFAULT 0,
	UNIQUE KEY packageID (packageID, environment, eventClassName, eventName, listenerClassName)
);

DROP TABLE IF EXISTS wcf1_import_mapping;
CREATE TABLE wcf1_import_mapping (
	objectTypeID INT(10) NOT NULL,
	oldID VARCHAR(255) NOT NULL,
	newID INT(10) NOT NULL,
	UNIQUE KEY (objectTypeID, oldID)
);

DROP TABLE IF EXISTS wcf1_label;
CREATE TABLE wcf1_label (
	labelID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10) NOT NULL,
	label VARCHAR(80) NOT NULL,
	cssClassName VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_label_group;
CREATE TABLE wcf1_label_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(80) NOT NULL,
	forceSelection TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_label_group_to_object;
CREATE TABLE wcf1_label_group_to_object (
	groupID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NULL
);

DROP TABLE IF EXISTS wcf1_label_object;
CREATE TABLE wcf1_label_object (
	labelID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	
	KEY (objectTypeID, labelID),
	KEY (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_language;
CREATE TABLE wcf1_language (
	languageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageCode VARCHAR(20) NOT NULL DEFAULT '',
	languageName VARCHAR(255) NOT NULL DEFAULT '',
	countryCode VARCHAR(10) NOT NULL DEFAULT '',
	isDefault TINYINT(1) NOT NULL DEFAULT 0,
	hasContent TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY languageCode (languageCode)
);

DROP TABLE IF EXISTS wcf1_language_category;
CREATE TABLE wcf1_language_category (
	languageCategoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageCategory VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY languageCategory (languageCategory)
);

DROP TABLE IF EXISTS wcf1_language_item;
CREATE TABLE wcf1_language_item (
	languageItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageID INT(10) NOT NULL,
	languageItem VARCHAR(255) NOT NULL DEFAULT '',
	languageItemValue MEDIUMTEXT NOT NULL,
	languageCustomItemValue MEDIUMTEXT,
	languageUseCustomValue TINYINT(1) NOT NULL DEFAULT 0,
	languageItemOriginIsSystem TINYINT(1) NOT NULL DEFAULT 1,
	languageCategoryID INT(10) NOT NULL,
	packageID INT(10),
	UNIQUE KEY languageItem (languageItem, languageID),
	KEY languageItemOriginIsSystem (languageItemOriginIsSystem)
);

DROP TABLE IF EXISTS wcf1_language_server;
CREATE TABLE wcf1_language_server (
	languageServerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	serverURL VARCHAR(255) NOT NULL DEFAULT '',
	isDisabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_like;
CREATE TABLE wcf1_like (
	likeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	objectID INT(10) NOT NULL DEFAULT 0,
	objectTypeID INT(10) NOT NULL,
	objectUserID INT(10),
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	likeValue TINYINT(1) NOT NULL DEFAULT 1,
	UNIQUE KEY (objectTypeID, objectID, userID)
);

DROP TABLE IF EXISTS wcf1_like_object;
CREATE TABLE wcf1_like_object (
	likeObjectID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL DEFAULT 0, 
	objectUserID INT(10),
	likes MEDIUMINT(7) NOT NULL DEFAULT 0,
	dislikes MEDIUMINT(7) NOT NULL DEFAULT 0,
	cumulativeLikes MEDIUMINT(7) NOT NULL DEFAULT 0,
	cachedUsers TEXT,
	UNIQUE KEY (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_moderation_queue;
CREATE TABLE wcf1_moderation_queue (
	queueID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	containerID INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NULL,
	time INT(10) NOT NULL DEFAULT 0,
	
	-- internal
	assignedUserID INT(10) NULL,
	status TINYINT(1) NOT NULL DEFAULT 0,
	comment TEXT,
	lastChangeTime INT(10) NOT NULL DEFAULT 0,
	
	-- additional data, e.g. message if reporting content
	additionalData TEXT,
	
	UNIQUE KEY affectedObject (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_moderation_queue_to_user;
CREATE TABLE wcf1_moderation_queue_to_user (
	queueID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	isAffected TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY queue (queueID, userID),
	KEY affected (queueID, userID, isAffected)
);

DROP TABLE IF EXISTS wcf1_modification_log;
CREATE TABLE wcf1_modification_log (
	logID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	action VARCHAR(80) NOT NULL,
	additionalData MEDIUMTEXT
);

DROP TABLE IF EXISTS wcf1_object_type;
CREATE TABLE wcf1_object_type (
	objectTypeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	definitionID INT(10) NOT NULL,
	packageID INT(10) NOT NULL,
	objectType VARCHAR(255) NOT NULL,
	className VARCHAR(255) NOT NULL DEFAULT '',
	additionalData MEDIUMTEXT,
	UNIQUE KEY objectType (objectType, definitionID, packageID)
);

DROP TABLE IF EXISTS wcf1_object_type_definition;
CREATE TABLE wcf1_object_type_definition (
	definitionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	definitionName VARCHAR(255) NOT NULL,
	packageID INT(10) NOT NULL,
	interfaceName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(80) NOT NULL DEFAULT '',
	UNIQUE KEY definitionName (definitionName)
);

DROP TABLE IF EXISTS wcf1_option;
CREATE TABLE wcf1_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	optionName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	optionValue MEDIUMTEXT,
	validationPattern TEXT,
	selectOptions MEDIUMTEXT,
	enableOptions MEDIUMTEXT,
	showOrder INT(10) NOT NULL DEFAULT 0,
	hidden TINYINT(1) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	supportI18n TINYINT(1) NOT NULL DEFAULT 0,
	requireI18n TINYINT(1) NOT NULL DEFAULT 0,
	additionalData MEDIUMTEXT,
	
	UNIQUE KEY optionName (optionName)
);

DROP TABLE IF EXISTS wcf1_option_category;
CREATE TABLE wcf1_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_package;
CREATE TABLE wcf1_package (
	packageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	package VARCHAR(255) NOT NULL DEFAULT '',
	packageDir VARCHAR(255) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	packageDescription VARCHAR(255) NOT NULL DEFAULT '',
	packageVersion VARCHAR(255) NOT NULL DEFAULT '',
	packageDate INT(10) NOT NULL DEFAULT 0,
	installDate INT(10) NOT NULL DEFAULT 0,
	updateDate INT(10) NOT NULL DEFAULT 0,
	packageURL VARCHAR(255) NOT NULL DEFAULT '',
	isApplication TINYINT(1) NOT NULL DEFAULT 0,
	author VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	KEY package (package)
);

DROP TABLE IF EXISTS wcf1_package_exclusion;
CREATE TABLE wcf1_package_exclusion (
	packageID INT(10) NOT NULL,
	excludedPackage VARCHAR(255) NOT NULL DEFAULT '',
	excludedPackageVersion VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY packageID (packageID, excludedPackage)
);

DROP TABLE IF EXISTS wcf1_package_installation_file_log;
CREATE TABLE wcf1_package_installation_file_log (
	packageID INT(10),
	filename VARCHAR(255) NOT NULL,
	application VARCHAR(20) NOT NULL,
	UNIQUE KEY applicationFile (application, filename)
);

DROP TABLE IF EXISTS wcf1_package_installation_form;
CREATE TABLE wcf1_package_installation_form (
	queueID INT(10) NOT NULL,
	formName VARCHAR(80) NOT NULL DEFAULT '',
	document TEXT NOT NULL,
	UNIQUE KEY formDocument (queueID, formName)
);

DROP TABLE IF EXISTS wcf1_package_installation_node;
CREATE TABLE wcf1_package_installation_node (
	queueID INT(10) NOT NULL,
	processNo INT(10) NOT NULL DEFAULT 0,
	sequenceNo SMALLINT(4) NOT NULL DEFAULT 0,
	node CHAR(8) NOT NULL DEFAULT '',
	parentNode CHAR(8) NOT NULL DEFAULT '',
	nodeType ENUM('optionalPackages','package', 'pip') NOT NULL DEFAULT 'package',
	nodeData TEXT NOT NULL,
	done TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_package_installation_plugin;
CREATE TABLE wcf1_package_installation_plugin (
	pluginName VARCHAR(255) NOT NULL PRIMARY KEY,
	packageID INT(10),
	priority TINYINT(1) NOT NULL DEFAULT 0,
	className VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS wcf1_package_installation_queue;
CREATE TABLE wcf1_package_installation_queue (
	queueID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parentQueueID INT(10) NOT NULL DEFAULT 0,
	processNo INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NOT NULL,
	package VARCHAR(255) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	packageID INT(10),
	archive VARCHAR(255) NOT NULL DEFAULT '',
	action ENUM('install', 'update', 'uninstall') NOT NULL DEFAULT 'install',
	done TINYINT(1) NOT NULL DEFAULT 0,
	confirmInstallation TINYINT(1) NOT NULL DEFAULT 0,
	packageType ENUM('default', 'requirement', 'optional') NOT NULL DEFAULT 'default'
);

DROP TABLE IF EXISTS wcf1_package_installation_sql_log;
CREATE TABLE wcf1_package_installation_sql_log ( 
	packageID INT(10), 
	sqlTable VARCHAR(100) NOT NULL DEFAULT '', 
	sqlColumn VARCHAR(100) NOT NULL DEFAULT '', 
	sqlIndex VARCHAR(100) NOT NULL DEFAULT '',
	UNIQUE KEY packageID (packageID, sqlTable, sqlColumn, sqlIndex) 
);

/* SQL_PARSER_OFFSET */

DROP TABLE IF EXISTS wcf1_package_requirement;
CREATE TABLE wcf1_package_requirement (
	packageID INT(10) NOT NULL,
	requirement INT(10) NOT NULL,
	UNIQUE KEY packageID (packageID, requirement)
);

DROP TABLE IF EXISTS wcf1_package_update;
CREATE TABLE wcf1_package_update (
	packageUpdateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageUpdateServerID INT(10) NOT NULL,
	package VARCHAR(255) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	packageDescription VARCHAR(255) NOT NULL DEFAULT '',
	author VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	isApplication TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY packageUpdateServerID (packageUpdateServerID, package)
);

DROP TABLE IF EXISTS wcf1_package_update_exclusion;
CREATE TABLE wcf1_package_update_exclusion (
	packageUpdateVersionID INT(10) NOT NULL,
	excludedPackage VARCHAR(255) NOT NULL DEFAULT '',
	excludedPackageVersion VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY packageUpdateVersionID (packageUpdateVersionID, excludedPackage)
);

DROP TABLE IF EXISTS wcf1_package_update_fromversion;
CREATE TABLE wcf1_package_update_fromversion (
	packageUpdateVersionID INT(10) NOT NULL DEFAULT 0,
	fromversion VARCHAR(50) NOT NULL DEFAULT '',
	UNIQUE KEY packageUpdateVersionID (packageUpdateVersionID, fromversion)
);

DROP TABLE IF EXISTS wcf1_package_update_optional;
CREATE TABLE wcf1_package_update_optional (
	packageUpdateVersionID INT(10) NOT NULL DEFAULT 0,
	package VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_package_update_requirement;
CREATE TABLE wcf1_package_update_requirement (
	packageUpdateVersionID INT(10) NOT NULL,
	package VARCHAR(255) NOT NULL DEFAULT '',
	minversion VARCHAR(50) NOT NULL DEFAULT '',
	UNIQUE KEY packageUpdateVersionID (packageUpdateVersionID, package)
);

DROP TABLE IF EXISTS wcf1_package_update_server;
CREATE TABLE wcf1_package_update_server (
	packageUpdateServerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	serverURL VARCHAR(255) NOT NULL DEFAULT '',
	loginUsername VARCHAR(255) NOT NULL DEFAULT '',
	loginPassword VARCHAR(255) NOT NULL DEFAULT '',
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	status ENUM('online', 'offline') NOT NULL DEFAULT 'online',
	errorMessage TEXT
);

DROP TABLE IF EXISTS wcf1_package_update_version;
CREATE TABLE wcf1_package_update_version (
	packageUpdateVersionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageUpdateID INT(10) NOT NULL,
	packageVersion VARCHAR(50) NOT NULL DEFAULT '',
	packageDate INT(10) NOT NULL DEFAULT 0,
	filename VARCHAR(255) NOT NULL DEFAULT '',
	license VARCHAR(255) NOT NULL DEFAULT '',
	licenseURL VARCHAR(255) NOT NULL DEFAULT '',
	isAccessible TINYINT(1) NOT NULL DEFAULT 1,
	isCritical TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY packageUpdateID (packageUpdateID, packageVersion)
);

DROP TABLE IF EXISTS wcf1_page_menu_item;
CREATE TABLE wcf1_page_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(255) NOT NULL DEFAULT '',
	menuItemController VARCHAR(255) NOT NULL DEFAULT '',
	menuItemLink VARCHAR(255) NOT NULL DEFAULT '',
	menuPosition ENUM('header', 'footer') NOT NULL DEFAULT 'header',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT NULL,
	options TEXT NULL,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	className VARCHAR(255) NOT NULL DEFAULT '',
	isLandingPage TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY (packageID, menuItem)
);

DROP TABLE IF EXISTS wcf1_poll;
CREATE TABLE wcf1_poll (
	pollID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL DEFAULT 0,
	question VARCHAR(255) DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	endTime INT(10) NOT NULL DEFAULT 0,
	isChangeable TINYINT(1) NOT NULL DEFAULT 0,
	isPublic TINYINT(1) NOT NULL DEFAULT 0,
	sortByVotes TINYINT(1) NOT NULL DEFAULT 0,
	resultsRequireVote TINYINT(1) NOT NULL DEFAULT 0,
	maxVotes INT(10) NOT NULL DEFAULT 1,
	votes INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_poll_option;
CREATE TABLE wcf1_poll_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	pollID INT(10) NOT NULL,
	optionValue VARCHAR(255) NOT NULL DEFAULT '',
	votes INT(10) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_poll_option_vote;
CREATE TABLE wcf1_poll_option_vote (
	pollID INT(10) NOT NULL,
	optionID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	
	KEY (optionID, userID),
	UNIQUE KEY vote (pollID, optionID, userID)
);

DROP TABLE IF EXISTS wcf1_search;
CREATE TABLE wcf1_search (
	searchID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10),
	searchData MEDIUMTEXT NOT NULL,
	searchTime INT(10) NOT NULL DEFAULT 0,
	searchType VARCHAR(255) NOT NULL DEFAULT '',
	searchHash CHAR(40) NOT NULL DEFAULT '',
	KEY searchHash (searchHash)
);

DROP TABLE IF EXISTS wcf1_search_index;
CREATE TABLE wcf1_search_index (
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	subject VARCHAR(255) NOT NULL DEFAULT '',
	message MEDIUMTEXT,
	metaData MEDIUMTEXT,
	time INT(10) NOT NULL DEFAULT 0,
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	languageID INT(10),
	UNIQUE KEY (objectTypeID, objectID, languageID),
	FULLTEXT INDEX fulltextIndex (subject, message, metaData),
	FULLTEXT INDEX fulltextIndexSubjectOnly (subject),
	KEY (userID, objectTypeID, time)
);

DROP TABLE IF EXISTS wcf1_search_keyword;
CREATE TABLE wcf1_search_keyword (
	keywordID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	keyword VARCHAR(255) NOT NULL,
	searches INT(10) NOT NULL DEFAULT 0,
	lastSearchTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (keyword),
	KEY (searches, lastSearchTime)
);

DROP TABLE IF EXISTS wcf1_session;
CREATE TABLE wcf1_session (
	sessionID CHAR(40) NOT NULL PRIMARY KEY,
	userID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(7) NOT NULL DEFAULT '',
	controller VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectType VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectID INT(10) NOT NULL DEFAULT 0,
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	objectID INT(10) NOT NULL DEFAULT 0,
	sessionVariables MEDIUMTEXT,
	spiderID INT(10),
	KEY packageID (lastActivityTime, spiderID)
);

DROP TABLE IF EXISTS wcf1_sitemap;
CREATE TABLE wcf1_sitemap (
	sitemapID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	sitemapName VARCHAR(80) NOT NULL DEFAULT '',
	className VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY sitemapName (packageID, sitemapName)
);

DROP TABLE IF EXISTS wcf1_smiley;
CREATE TABLE wcf1_smiley (
	smileyID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryID INT(10),
	smileyPath VARCHAR(255) NOT NULL DEFAULT '',
	smileyTitle VARCHAR(255) NOT NULL DEFAULT '',
	smileyCode VARCHAR(255) NOT NULL DEFAULT '',
	aliases TEXT NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY smileyCode (smileyCode)
);

DROP TABLE IF EXISTS wcf1_spider;
CREATE TABLE wcf1_spider (
	spiderID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	spiderIdentifier VARCHAR(255) DEFAULT '',
	spiderName VARCHAR(255) DEFAULT '',
	spiderURL VARCHAR(255) DEFAULT '',
	UNIQUE KEY spiderIdentifier (spiderIdentifier)
);

DROP TABLE IF EXISTS wcf1_style;
CREATE TABLE wcf1_style (
	styleID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	styleName VARCHAR(255) NOT NULL DEFAULT '',
	templateGroupID INT(10) NOT NULL DEFAULT 0,
	isDefault TINYINT(1) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	styleDescription VARCHAR(30) NOT NULL DEFAULT '',
	styleVersion VARCHAR(255) NOT NULL DEFAULT '',
	styleDate CHAR(10) NOT NULL DEFAULT '0000-00-00',
	image VARCHAR(255) NOT NULL DEFAULT '',
	copyright VARCHAR(255) NOT NULL DEFAULT '',
	license VARCHAR(255) NOT NULL DEFAULT '',
	authorName VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	imagePath VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_style_variable;
CREATE TABLE wcf1_style_variable (
	variableID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	variableName VARCHAR(50) NOT NULL,
	defaultValue MEDIUMTEXT,
	UNIQUE KEY variableName (variableName)
);

DROP TABLE IF EXISTS wcf1_style_variable_value;
CREATE TABLE wcf1_style_variable_value (
	styleID INT(10) NOT NULL,
	variableID INT(10) NOT NULL,
	variableValue MEDIUMTEXT,
	UNIQUE KEY (styleID, variableID)
);

DROP TABLE IF EXISTS wcf1_tag;
CREATE TABLE wcf1_tag (
	tagID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageID INT(10) NOT NULL DEFAULT 0,
	name VARCHAR(255) NOT NULL,
	synonymFor INT(10),
	UNIQUE KEY (languageID, name)
);

DROP TABLE IF EXISTS wcf1_tag_to_object;
CREATE TABLE wcf1_tag_to_object (
	objectID INT(10) NOT NULL,
	tagID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	languageID INT(10) NOT NULL,
	UNIQUE KEY (objectTypeID, languageID, objectID, tagID),
	KEY (objectTypeID, languageID, tagID),
	KEY (tagID, objectTypeID)
);

DROP TABLE IF EXISTS wcf1_template;
CREATE TABLE wcf1_template (
	templateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	templateName VARCHAR(255) NOT NULL,
	application VARCHAR(20) NOT NULL,
	templateGroupID INT(10),
	lastModificationTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY applicationTemplate (application, templateGroupID, templateName),
	KEY templateGroupID (packageID, templateGroupID, templateName)
);

DROP TABLE IF EXISTS wcf1_template_group;
CREATE TABLE wcf1_template_group (
	templateGroupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parentTemplateGroupID INT(10),
	templateGroupName VARCHAR(255) NOT NULL DEFAULT '',
	templateGroupFolderName VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_template_listener;
CREATE TABLE wcf1_template_listener (
	listenerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	name VARCHAR(80) NOT NULL DEFAULT '',
	environment ENUM('user','admin') NOT NULL DEFAULT 'user',
	templateName VARCHAR(80) NOT NULL DEFAULT '',
	eventName VARCHAR(50) NOT NULL DEFAULT '',
	templateCode TEXT NOT NULL,
	KEY templateName (environment, templateName)
);

DROP TABLE IF EXISTS wcf1_tracked_visit;
CREATE TABLE wcf1_tracked_visit (
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	visitTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (objectTypeID, objectID, userID),
	KEY (userID, visitTime)
);

DROP TABLE IF EXISTS wcf1_tracked_visit_type;
CREATE TABLE wcf1_tracked_visit_type (
	objectTypeID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	visitTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (objectTypeID, userID),
	KEY (userID, visitTime)
);

DROP TABLE IF EXISTS wcf1_user;
CREATE TABLE wcf1_user (
	userID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(255) NOT NULL DEFAULT '',
	email VARCHAR(255) NOT NULL DEFAULT '',
	password VARCHAR(100) NOT NULL DEFAULT '',
	accessToken CHAR(40) NOT NULL DEFAULT '',
	languageID INT(10) NOT NULL DEFAULT 0,
	registrationDate INT(10) NOT NULL DEFAULT 0,
	styleID INT(10) NOT NULL DEFAULT 0,
	banned TINYINT(1) NOT NULL DEFAULT 0,
	banReason MEDIUMTEXT NULL,
	activationCode INT(10) NOT NULL DEFAULT 0,
	lastLostPasswordRequestTime INT(10) NOT NULL DEFAULT 0,
	lostPasswordKey VARCHAR(40) NOT NULL DEFAULT '',
	lastUsernameChange INT(10) NOT NULL DEFAULT 0,
	newEmail VARCHAR(255) NOT NULL DEFAULT '',
	oldUsername VARCHAR(255) NOT NULL DEFAULT '',
	quitStarted INT(10) NOT NULL DEFAULT 0,
	reactivationCode INT(10) NOT NULL DEFAULT 0,
	registrationIpAddress VARCHAR(39) NOT NULL DEFAULT '',
	avatarID INT(10),
	disableAvatar TINYINT(1) NOT NULL DEFAULT 0,
	disableAvatarReason TEXT,
	enableGravatar TINYINT(1) NOT NULL DEFAULT 0,
	signature TEXT,
	signatureEnableBBCodes TINYINT(1) NOT NULL DEFAULT 1,
	signatureEnableHtml TINYINT(1) NOT NULL DEFAULT 0,
	signatureEnableSmilies TINYINT(1) NOT NULL DEFAULT 1,
	disableSignature TINYINT(1) NOT NULL DEFAULT 0,
	disableSignatureReason TEXT,
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	profileHits INT(10) NOT NULL DEFAULT 0,
	rankID INT(10),
	userTitle VARCHAR(255) NOT NULL DEFAULT '',
	userOnlineGroupID INT(10),
	activityPoints INT(10) NOT NULL DEFAULT 0,
	notificationMailToken VARCHAR(20) NOT NULL DEFAULT '',
	authData VARCHAR(255) NOT NULL DEFAULT '',
	likesReceived MEDIUMINT(7) NOT NULL DEFAULT 0,
	
	KEY username (username),
	KEY registrationDate (registrationDate),
	KEY styleID (styleID),
	KEY activationCode (activationCode),
	KEY registrationData (registrationIpAddress, registrationDate),
	KEY activityPoints (activityPoints),
	KEY likesReceived (likesReceived)
);

DROP TABLE IF EXISTS wcf1_user_activity_event;
CREATE TABLE wcf1_user_activity_event (
	eventID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	languageID INT(10),
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL,
	additionalData TEXT,
	
	KEY (time),
	KEY (userID, time),
	KEY (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_user_activity_point;
CREATE TABLE wcf1_user_activity_point (
	userID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	activityPoints INT(10) NOT NULL DEFAULT 0,
	items INT(10) NOT NULL DEFAULT 0,
	PRIMARY KEY (userID, objectTypeID),
	KEY (objectTypeID)
);

DROP TABLE IF EXISTS wcf1_user_avatar;
CREATE TABLE wcf1_user_avatar (
	avatarID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	avatarName VARCHAR(255) NOT NULL DEFAULT '',
	avatarExtension VARCHAR(7) NOT NULL DEFAULT '',
	width SMALLINT(5) NOT NULL DEFAULT 0,
	height SMALLINT(5) NOT NULL DEFAULT 0,
	userID INT(10),
	fileHash VARCHAR(40) NOT NULL DEFAULT '',
	cropX SMALLINT(5) NOT NULL DEFAULT 0,
	cropY SMALLINT(5) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_collapsible_content;
CREATE TABLE wcf1_user_collapsible_content (
	objectTypeID INT(10) NOT NULL,
	objectID VARCHAR(50) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY (objectTypeID, objectID, userID)
);

DROP TABLE IF EXISTS wcf1_user_follow;
CREATE TABLE wcf1_user_follow (
	followID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	followUserID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (userID, followUserID)
);

DROP TABLE IF EXISTS wcf1_user_group;
CREATE TABLE wcf1_user_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(255) NOT NULL DEFAULT '',
	groupType TINYINT(1) NOT NULL DEFAULT 4,
	priority MEDIUMINT(8) NOT NULL DEFAULT 0,
	userOnlineMarking VARCHAR(255) NOT NULL DEFAULT '%s',
	showOnTeamPage TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_group_option;
CREATE TABLE wcf1_user_group_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	optionName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue MEDIUMTEXT,
	validationPattern TEXT,
	enableOptions MEDIUMTEXT,
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	additionalData MEDIUMTEXT,
	UNIQUE KEY optionName (optionName, packageID)
);

DROP TABLE IF EXISTS wcf1_user_group_option_category;
CREATE TABLE wcf1_user_group_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_user_group_option_value;
CREATE TABLE wcf1_user_group_option_value (
	groupID INT(10) NOT NULL,
	optionID INT(10) NOT NULL,
	optionValue MEDIUMTEXT NOT NULL,
	UNIQUE KEY groupID (groupID, optionID)
);

DROP TABLE IF EXISTS wcf1_user_ignore;
CREATE TABLE wcf1_user_ignore (
	ignoreID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	ignoreUserID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (userID, ignoreUserID)
);

DROP TABLE IF EXISTS wcf1_user_menu_item;
CREATE TABLE wcf1_user_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(255) NOT NULL DEFAULT '',
	menuItemController VARCHAR(255) NOT NULL DEFAULT '',
	menuItemLink VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	className VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY menuItem (menuItem, packageID)
);

-- notifications
DROP TABLE IF EXISTS wcf1_user_notification;
CREATE TABLE wcf1_user_notification (
	notificationID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	eventID INT(10) NOT NULL,
	objectID INT(10) NOT NULL DEFAULT 0,
	eventHash VARCHAR(40) NOT NULL DEFAULT '',
	authorID INT(10),
	time INT(10) NOT NULL DEFAULT 0,
	additionalData TEXT,
	KEY (eventHash),
	UNIQUE KEY (packageID, eventID, objectID)
);

-- notification recipients
DROP TABLE IF EXISTS wcf1_user_notification_to_user;
CREATE TABLE wcf1_user_notification_to_user (
	notificationID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	mailNotified TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY notificationID (notificationID, userID)
);

-- events that create notifications
DROP TABLE IF EXISTS wcf1_user_notification_event;
CREATE TABLE wcf1_user_notification_event (
	eventID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	eventName VARCHAR(255) NOT NULL DEFAULT '',
	objectTypeID INT(10) NOT NULL,
	className VARCHAR(255) NOT NULL DEFAULT '',
	permissions TEXT,
	options TEXT,
	preset TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY eventName (eventName, objectTypeID)
);

-- user configuration for events
DROP TABLE IF EXISTS wcf1_user_notification_event_to_user;
CREATE TABLE wcf1_user_notification_event_to_user (
	userID INT(10) NOT NULL,
	eventID INT(10) NOT NULL,
	mailNotificationType ENUM('none', 'instant', 'daily') NOT NULL DEFAULT 'none',
	UNIQUE KEY (eventID, userID)
);

DROP TABLE IF EXISTS wcf1_user_object_watch;
CREATE TABLE wcf1_user_object_watch (
	watchID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	notification TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (objectTypeID, userID, objectID),
	KEY (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_user_option;
CREATE TABLE wcf1_user_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	optionName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue MEDIUMTEXT,
	validationPattern TEXT,
	selectOptions MEDIUMTEXT,
	enableOptions MEDIUMTEXT,
	required TINYINT(1) NOT NULL DEFAULT 0,
	askDuringRegistration TINYINT(1) NOT NULL DEFAULT 0,
	editable TINYINT(1) NOT NULL DEFAULT 0, 
	visible TINYINT(1) NOT NULL DEFAULT 0, 
	outputClass VARCHAR(255) NOT NULL DEFAULT '',
	searchable TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	additionalData MEDIUMTEXT,
	UNIQUE KEY optionName (optionName, packageID),
	KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_user_option_category;
CREATE TABLE wcf1_user_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_user_option_value;
CREATE TABLE wcf1_user_option_value (
	userID INT(10) NOT NULL PRIMARY KEY
);

DROP TABLE IF EXISTS wcf1_user_profile_menu_item;
CREATE TABLE wcf1_user_profile_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT NULL,
	options TEXT NULL,
	className VARCHAR(255) NOT NULL,
	UNIQUE KEY (packageID, menuItem)
);

DROP TABLE IF EXISTS wcf1_user_profile_visitor;
CREATE TABLE wcf1_user_profile_visitor (
	visitorID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	ownerID INT(10),
	userID INT(10),
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (ownerID, userID),
	KEY (time)
);

DROP TABLE IF EXISTS wcf1_user_rank;
CREATE TABLE wcf1_user_rank (
	rankID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10),
	requiredPoints INT(10) NOT NULL DEFAULT 0,
	rankTitle VARCHAR(255) NOT NULL DEFAULT '',
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	rankImage VARCHAR(255) NOT NULL DEFAULT '',
	repeatImage TINYINT(3) NOT NULL DEFAULT 1,
	requiredGender TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_storage;
CREATE TABLE wcf1_user_storage (
	userID INT(10) NOT NULL,
	field VARCHAR(80) NOT NULL DEFAULT '',
	fieldValue TEXT,
	UNIQUE KEY userStorageData (userID, field)
);

DROP TABLE IF EXISTS wcf1_user_to_group;
CREATE TABLE wcf1_user_to_group (
	userID INT(10) NOT NULL,
	groupID INT(10) NOT NULL,
	UNIQUE KEY userID (userID, groupID)
);

DROP TABLE IF EXISTS wcf1_user_to_language;
CREATE TABLE wcf1_user_to_language (
	userID INT(10) NOT NULL,
	languageID INT(10) NOT NULL,
	UNIQUE KEY userID (userID, languageID)
);

/* SQL_PARSER_OFFSET */

/* foreign keys */
ALTER TABLE wcf1_acl_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_option ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_option_category ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_option_to_user ADD FOREIGN KEY (optionID) REFERENCES wcf1_acl_option (optionID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_option_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_option_to_group ADD FOREIGN KEY (optionID) REFERENCES wcf1_acl_option (optionID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_option_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_search_provider ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (sessionLogID) REFERENCES wcf1_acp_session_log (sessionLogID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_application ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_attachment ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_attachment ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_bbcode ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_bbcode_attribute ADD FOREIGN KEY (bbcodeID) REFERENCES wcf1_bbcode (bbcodeID) ON DELETE CASCADE;

ALTER TABLE wcf1_category ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_action ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_item ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_page ADD FOREIGN KEY (actionID) REFERENCES wcf1_clipboard_action (actionID) ON DELETE CASCADE;
ALTER TABLE wcf1_clipboard_page ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_core_object ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_cronjob ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_cronjob_log ADD FOREIGN KEY (cronjobID) REFERENCES wcf1_cronjob (cronjobID) ON DELETE CASCADE;

ALTER TABLE wcf1_event_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_language_item ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_language_item ADD FOREIGN KEY (languageCategoryID) REFERENCES wcf1_language_category (languageCategoryID) ON DELETE CASCADE;
ALTER TABLE wcf1_language_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_object_type ADD FOREIGN KEY (definitionID) REFERENCES wcf1_object_type_definition (definitionID) ON DELETE CASCADE;
ALTER TABLE wcf1_object_type ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_object_type_definition ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_exclusion ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_file_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_form ADD FOREIGN KEY (queueID) REFERENCES wcf1_package_installation_queue (queueID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_node ADD FOREIGN KEY (queueID) REFERENCES wcf1_package_installation_queue (queueID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_plugin ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_queue ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_installation_queue ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_package_installation_sql_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_package_requirement ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_requirement ADD FOREIGN KEY (requirement) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update ADD FOREIGN KEY (packageUpdateServerID) REFERENCES wcf1_package_update_server (packageUpdateServerID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_exclusion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_fromversion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_requirement ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_optional ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_version ADD FOREIGN KEY (packageUpdateID) REFERENCES wcf1_package_update (packageUpdateID) ON DELETE CASCADE;

ALTER TABLE wcf1_page_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_search ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_session ADD FOREIGN KEY (spiderID) REFERENCES wcf1_spider (spiderID) ON DELETE CASCADE;

ALTER TABLE wcf1_sitemap ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_smiley ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_smiley ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_storage ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_style ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_variable_value ADD FOREIGN KEY (styleID) REFERENCES wcf1_style (styleID) ON DELETE CASCADE;
ALTER TABLE wcf1_style_variable_value ADD FOREIGN KEY (variableID) REFERENCES wcf1_style_variable (variableID) ON DELETE CASCADE;

ALTER TABLE wcf1_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_template ADD FOREIGN KEY (templateGroupID) REFERENCES wcf1_template_group (templateGroupID) ON DELETE CASCADE;

ALTER TABLE wcf1_template_group ADD FOREIGN KEY (parentTemplateGroupID) REFERENCES wcf1_template_group (templateGroupID) ON DELETE SET NULL;

ALTER TABLE wcf1_template_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option_value ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_group_option_value ADD FOREIGN KEY (optionID) REFERENCES wcf1_user_group_option (optionID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_user_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_option_value ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_to_group ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_to_language ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_to_language ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_dashboard_box ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_dashboard_option ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_dashboard_option ADD FOREIGN KEY (boxID) REFERENCES wcf1_dashboard_box (boxID) ON DELETE CASCADE;

ALTER TABLE wcf1_import_mapping ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_tracked_visit ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_tracked_visit ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_tracked_visit_type ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_tracked_visit_type ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user ADD FOREIGN KEY (avatarID) REFERENCES wcf1_user_avatar (avatarID) ON DELETE SET NULL;
ALTER TABLE wcf1_user ADD FOREIGN KEY (rankID) REFERENCES wcf1_user_rank (rankID) ON DELETE SET NULL;
ALTER TABLE wcf1_user ADD FOREIGN KEY (userOnlineGroupID) REFERENCES wcf1_user_group (groupID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_avatar ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_follow ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_follow ADD FOREIGN KEY (followUserID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_ignore ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_ignore ADD FOREIGN KEY (ignoreUserID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (authorID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (notificationID) REFERENCES wcf1_user_notification (notificationID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_profile_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_user_rank ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_activity_point ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_point ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_profile_visitor ADD FOREIGN KEY (ownerID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_profile_visitor ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_object_watch ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_object_watch ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (assignedUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_moderation_queue_to_user ADD FOREIGN KEY (queueID) REFERENCES wcf1_moderation_queue (queueID) ON DELETE CASCADE;
ALTER TABLE wcf1_moderation_queue_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_like ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_like ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_like ADD FOREIGN KEY (objectUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_like_object ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_like_object ADD FOREIGN KEY (objectUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_comment ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_comment ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_comment_response ADD FOREIGN KEY (commentID) REFERENCES wcf1_comment (commentID) ON DELETE CASCADE;
ALTER TABLE wcf1_comment_response ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_label ADD FOREIGN KEY (groupID) REFERENCES wcf1_label_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_label_group_to_object ADD FOREIGN KEY (groupID) REFERENCES wcf1_label_group (groupID) ON DELETE CASCADE;
ALTER TABLE wcf1_label_group_to_object ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_label_object ADD FOREIGN KEY (labelID) REFERENCES wcf1_label (labelID) ON DELETE CASCADE;
ALTER TABLE wcf1_label_object ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_tag ADD FOREIGN KEY (synonymFor) REFERENCES wcf1_tag (tagID) ON DELETE CASCADE;

ALTER TABLE wcf1_tag_to_object ADD FOREIGN KEY (tagID) REFERENCES wcf1_tag (tagID) ON DELETE CASCADE;
ALTER TABLE wcf1_tag_to_object ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_tag_to_object ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_search_index ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_search_index ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_poll ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_poll_option ADD FOREIGN KEY (pollID) REFERENCES wcf1_poll (pollID) ON DELETE CASCADE;

ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (pollID) REFERENCES wcf1_poll (pollID) ON DELETE CASCADE;
ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (optionID) REFERENCES wcf1_poll_option (optionID) ON DELETE CASCADE;
ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

/* default inserts */
-- default user groups
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group1', 1);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group2', 2);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group3', 3);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group4', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group5', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group6', 4);

-- default user group options
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, showOrder) VALUES ('admin.general.canUseAcp', 'admin.general', 'boolean', '0', 1);
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, showOrder) VALUES ('admin.system.package.canInstallPackage', 'admin.system.package', 'boolean', '0', 1);
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, showOrder) VALUES ('admin.user.canEditGroup', 'admin.user.group', 'boolean', '0', 1);

-- default user group option values
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 1, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 2, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 3, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 1, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 2, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 3, '1');	-- Administrators

-- default update servers
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');

-- style default values
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBackgroundColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfColor', 'rgba(102, 102, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLinkColor', 'rgba(63, 127, 191, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLinkHoverColor', 'rgba(15, 79, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContainerBackgroundColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContainerAccentBackgroundColor', 'rgba(249, 249, 249, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContainerHoverBackgroundColor', 'rgba(244, 244, 244, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContainerBorderColor', 'rgba(221, 221, 221, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContainerBorderRadius', '0');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxBackgroundColor', 'rgba(63, 127, 191, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxHoverColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfUserPanelBackgroundColor', 'rgba(45, 45, 45, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfUserPanelColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfUserPanelHoverColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBackgroundColor', 'rgba(249, 249, 249, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBorderColor', 'rgba(221, 221, 221, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBorderRadius', '15px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSmallButtonBorderRadius', '3px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonColor', 'rgba(102, 102, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBackgroundColor', 'rgba(211, 232, 254, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBorderColor', 'rgba(136, 194, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryColor', 'rgba(102, 153, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryHoverBackgroundColor', 'darken(@wcfButtonPrimaryBackgroundColor, 3%)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryHoverBorderColor', 'darken(@wcfButtonPrimaryBorderColor, 10%)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryHoverColor', '@wcfButtonPrimaryColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonHoverBackgroundColor', 'rgba(241, 241, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonHoverBorderColor', 'rgba(224, 224, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonHoverColor', 'rgba(102, 102, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBackgroundColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputColor', 'rgba(102, 102, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderColor', 'rgba(204, 204, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputHoverBackgroundColor', 'rgba(239, 247, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputHoverBorderColor', 'rgba(198, 222, 248, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderRadius', '0');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfBaseFontSize', '13px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfBaseFontFamily', '"Trebuchet MS", Arial, sans-serif');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutFluidGap', '30px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutFixedWidth', '1200px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageBackgroundColor', 'rgba(224, 224, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageColor', 'rgba(102, 102, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageLinkColor', 'rgba(63, 127, 191, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageLinkHoverColor', 'rgba(15, 79, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarBackgroundColor', '@wcfContainerHoverBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDimmedColor', 'rgba(136, 136, 136, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfExtraDimmedColor', 'lighten(@wcfDimmedColor, 20%)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLabelColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeadlineColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeadlineFontFamily', '"Trebuchet MS", Arial, sans-serif');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackgroundColor', '@wcfContentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBorderColor', '@wcfContainerBorderColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownHoverBackgroundColor', '@wcfContainerHoverBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfBaseLineHeight', '1.28');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeadlineFontSize', '170%');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSubHeadlineFontSize', '140%');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTitleFontSize', '120%');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSmallFontSize', '85%');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfWarningColor', 'rgba(153, 153, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfWarningBackgroundColor', 'rgba(255, 255, 221, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfWarningBorderColor', 'rgba(204, 204, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfErrorColor', 'rgba(204, 0, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfErrorBackgroundColor', 'rgba(255, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfErrorBorderColor', 'rgba(255, 153, 153, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSuccessColor', 'rgba(0, 153, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSuccessBackgroundColor', 'rgba(238, 255, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSuccessBorderColor', 'rgba(0, 204, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInfoColor', 'rgba(102, 136, 187, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInfoBackgroundColor', 'rgba(221, 238, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInfoBorderColor', 'rgba(153, 187, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipBackgroundColor', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipColor', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHighlightBackgroundColor', 'rgba(255, 255, 102, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfGapTiny', '4px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfGapSmall', '7px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfGapMedium', '14px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfGapLarge', '21px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationHeaderBackgroundColor', '@wcfContentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationFooterBackgroundColor', '@wcfContainerAccentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfMainMenuBackgroundColor', '@wcfContainerAccentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfMainMenuHoverBackgroundColor', '@wcfContainerAccentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfMainMenuColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfMainMenuActiveColor', '@wcfLinkColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfUserPanelHoverBackgroundColor', 'rgba(60, 60, 60, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSelectedBackgroundColor', 'rgba(255, 255, 200, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSelectedColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDeletedBackgroundColor', 'rgba(255, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDeletedColor', 'rgba(204, 0, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDisabledBackgroundColor', 'rgba(238, 255, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDisabledColor', 'rgba(0, 153, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowLightColor', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowDarkColor', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useFluidLayout', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogo', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('individualLess', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('overrideLess', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('messageSidebarOrientation', 'left');

-- media providers
-- Videos
	-- Youtube
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouTube', 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/watch\\?(?:.*?&)?v=)(?P<ID>[a-zA-Z0-9_-]+)(?P<start>(?:#a?t=(?:\\d+|(?:\\d+h(?:\\d+m)?(?:\\d+s)?)|(?:\\d+m(?:\\d+s)?)|(?:\\d+s))$)?)', '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{$ID}?wmode=transparent{$start}" allowfullscreen></iframe>');
	-- Vimeo
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Vimeo', 'http://vimeo\\.com/(?P<ID>\\d+)', '<iframe src="http://player.vimeo.com/video/{$ID}" width="400" height="225" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
	-- MyVideo
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('MyVideo', 'http://(?:www\\.)?myvideo\\.de/watch/(?P<ID>\\d+)', '<object style="width:611px;height:383px;" width="611" height="383"><embed src="http://www.myvideo.de/movie/{$ID}" width="611" height="383" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed><param name="movie" value="http://www.myvideo.de/movie/{$ID}"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param></object>');
	-- Clipfish
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Clipfish', 'http://(?:www\\.)?clipfish\\.de/(?:.*?/)?video/(?P<ID>\\d+)/', '<div style="width:464px; height:404px;"><div style="width:464px; height:384px;"><iframe src="http://www.clipfish.de/embed_video/?vid={$ID}&amp;as=0&amp;col=990000" name="Clipfish Embedded Video" width="464" height="384" align="left" marginheight="0" marginwidth="0" scrolling="no"></iframe></div></div>');
	-- Veoh
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Veoh', 'http://(?:www\\.)?veoh\\.com/watch/v(?P<ID>\\d+[a-zA-Z0-9]+)', '<object width="410" height="341" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1308&amp;permalinkId=v{$ID}&amp;player=videodetailsembedded&amp;videoAutoPlay=0&amp;id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1308&amp;permalinkId=v{$ID}&amp;player=videodetailsembedded&amp;videoAutoPlay=0&amp;id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="410" height="341" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>');
	-- DailyMotion
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('DailyMotion', 'https?://(?:www\\.)?dailymotion\\.com/video/(?P<ID>[a-zA-Z0-9]+)', '<iframe width="480" height="208" src="http://www.dailymotion.com/embed/video/{$ID}"></iframe>');
	-- YouKu
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouKu', 'https?://(?:.+?\\.)?youku\\.com/v_show/id_(?P<ID>[a-zA-Z0-9_-]+)(?:\\.html)?', '<iframe height=498 width=510 src="http://player.youku.com/embed/{$ID}" allowfullscreen></iframe>');
-- Misc
	-- github gist
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('github gist', 'https://gist.github.com/(?P<ID>[^/]+/[0-9a-zA-Z]+)', '<script src="https://gist.github.com/{$ID}.js"> </script>');
	-- soundcloud
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Soundcloud', 'https?://soundcloud.com/(?P<artist>[a-zA-Z0-9_-]+)/(?P<song>[a-zA-Z0-9_-]+)', '<iframe width="100%" height="166" scrolling="no" src="https://w.soundcloud.com/player/?url=http%3A%2F%2Fsoundcloud.com%2F{$artist}%2F{$song}"></iframe>');

-- default priorities
UPDATE wcf1_user_group SET priority = 10 WHERE groupID = 3;
UPDATE wcf1_user_group SET priority = 1000 WHERE groupID = 4;
UPDATE wcf1_user_group SET priority = 50 WHERE groupID = 5;
UPDATE wcf1_user_group SET priority = 100 WHERE groupID = 6;

-- default 'showOnTeamPage' setting
UPDATE wcf1_user_group SET showOnTeamPage = 1 WHERE groupID IN (4, 5, 6);

-- default ranks
INSERT INTO wcf1_user_rank (groupID, requiredPoints, rankTitle, cssClassName) VALUES
	(4, 0, 'wcf.user.rank.administrator', 'blue'),
	(5, 0, 'wcf.user.rank.moderator', 'blue'),
	(6, 0, 'wcf.user.rank.superModerator', 'blue'),
	(3, 0, 'wcf.user.rank.user0', ''),
	(3, 300, 'wcf.user.rank.user1', ''),
	(3, 900, 'wcf.user.rank.user2', ''),
	(3, 3000, 'wcf.user.rank.user3', ''),
	(3, 9000, 'wcf.user.rank.user4', ''),
	(3, 15000, 'wcf.user.rank.user5', '');
