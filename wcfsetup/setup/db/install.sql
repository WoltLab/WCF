/* tables */
DROP TABLE IF EXISTS wcf1_acp_menu_item;
CREATE TABLE wcf1_acp_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(255) NOT NULL DEFAULT '',
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
	packageID INT(10),
	userID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(4) NOT NULL DEFAULT '',
	controller VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectType VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectID INT(10) NOT NULL DEFAULT 0,
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	objectID INT(10) NOT NULL DEFAULT 0,
	sessionVariables MEDIUMTEXT,
	KEY sessionID (sessionID, packageID)
);

DROP TABLE IF EXISTS wcf1_acp_session_access_log;
CREATE TABLE wcf1_acp_session_access_log (
	sessionAccessLogID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionLogID INT(10) NOT NULL,
	packageID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(4) NOT NULL DEFAULT '',
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
	templateName VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY (packageID, templateName)
);

DROP TABLE IF EXISTS wcf1_application;
CREATE TABLE wcf1_application (
	packageID INT(10) NOT NULL PRIMARY KEY,
	domainName VARCHAR(255) NOT NULL,
	domainPath VARCHAR(255) NOT NULL DEFAULT '/',
	cookieDomain VARCHAR(255) NOT NULL,
	cookieDomainPath VARCHAR(255) NOT NULL DEFAULT '/',
	groupID INT(10),
	isPrimary TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_application_group;
CREATE TABLE wcf1_application_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS wcf1_cache_resource;
CREATE TABLE wcf1_cache_resource (
	cacheResource VARCHAR(255) NOT NULL PRIMARY KEY
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

DROP TABLE IF EXISTS wcf1_cleanup_listener;
CREATE TABLE wcf1_cleanup_listener (
	listenerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	className VARCHAR(255) NOT NULL DEFAULT '',
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (className, packageID)
);

DROP TABLE IF EXISTS wcf1_cleanup_log;
CREATE TABLE wcf1_cleanup_log (
	packageID INT(10) NOT NULL DEFAULT 0,
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	objectID INT(10) NOT NULL DEFAULT 0,
	deleteTime INT(10) NOT NULL DEFAULT 0,
	KEY objectType (objectType)
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
	active TINYINT(1) NOT NULL DEFAULT 1,
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
	UNIQUE KEY languageItem (languageItem, packageID, languageID),
	KEY languageItemOriginIsSystem (languageItemOriginIsSystem)
);

DROP TABLE IF EXISTS wcf1_language_server;
CREATE TABLE wcf1_language_server (
	languageServerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	serverURL VARCHAR(255) NOT NULL DEFAULT '',
	disabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_language_to_package;
CREATE TABLE wcf1_language_to_package (
	languageID INT(10) NOT NULL,
	packageID INT(10),
	UNIQUE KEY languageID (languageID, packageID)
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
	UNIQUE KEY definitionName (definitionName, packageID)
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
	UNIQUE KEY optionName (optionName, packageID)
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
	UNIQUE KEY categoryName (categoryName, packageID)
);

DROP TABLE IF EXISTS wcf1_package;
CREATE TABLE wcf1_package (
	packageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	package VARCHAR(255) NOT NULL DEFAULT '',
	packageDir VARCHAR(255) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	instanceName VARCHAR(255) NOT NULL DEFAULT '',
	instanceNo INT(10) NOT NULL DEFAULT 1,
	packageDescription VARCHAR(255) NOT NULL DEFAULT '',
	packageVersion VARCHAR(255) NOT NULL DEFAULT '',
	packageDate INT(10) NOT NULL DEFAULT 0,
	installDate INT(10) NOT NULL DEFAULT 0,
	updateDate INT(10) NOT NULL DEFAULT 0,
	packageURL VARCHAR(255) NOT NULL DEFAULT '',
	parentPackageID INT(10) NOT NULL DEFAULT 0,
	isUnique TINYINT(1) NOT NULL DEFAULT 0,
	isApplication TINYINT(1) NOT NULL DEFAULT 0,
	author VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	packageIcon VARCHAR(30) NOT NULL DEFAULT '',
	KEY package (package)
);

DROP TABLE IF EXISTS wcf1_package_dependency;
CREATE TABLE wcf1_package_dependency (
	packageID INT(10) NOT NULL,
	dependency INT(10) NOT NULL,
	priority INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY packageID (packageID, dependency)
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
	filename VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY packageID (packageID, filename)
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
	action ENUM('install', 'update', 'uninstall', 'rollback') NOT NULL DEFAULT 'install',
	cancelable TINYINT(1) NOT NULL DEFAULT 1,
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

DROP TABLE IF EXISTS wcf1_package_requirement;
CREATE TABLE wcf1_package_requirement (
	packageID INT(10) NOT NULL,
	requirement INT(10) NOT NULL,
	UNIQUE KEY packageID (packageID, requirement)
);

DROP TABLE IF EXISTS wcf1_package_requirement_map;
CREATE TABLE wcf1_package_requirement_map (
	packageID INT(10) NOT NULL,
	requirement INT(10) NOT NULL,
	level INT(10) NOT NULL DEFAULT 0,
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
	plugin VARCHAR(255) NOT NULL DEFAULT '',
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
	disabled TINYINT(1) NOT NULL DEFAULT 0,
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	status ENUM('online', 'offline') NOT NULL DEFAULT 'online',
	errorMessage TEXT
);

DROP TABLE IF EXISTS wcf1_package_update_version;
CREATE TABLE wcf1_package_update_version (
	packageUpdateVersionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageUpdateID INT(10) NOT NULL,
	packageVersion VARCHAR(50) NOT NULL DEFAULT '',
	updateType VARCHAR(10) NOT NULL DEFAULT '',
	packageDate INT(10) NOT NULL DEFAULT 0,
	filename VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY packageUpdateID (packageUpdateID, packageVersion)
);

DROP TABLE IF EXISTS wcf1_page_menu_item;
CREATE TABLE wcf1_page_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(255) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(255) NOT NULL DEFAULT '',
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

DROP TABLE IF EXISTS wcf1_session;
CREATE TABLE wcf1_session (
	sessionID CHAR(40) NOT NULL PRIMARY KEY,
	packageID INT(10) NOT NULL,
	userID INT(10),
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(4) NOT NULL DEFAULT '',
	controller VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectType VARCHAR(255) NOT NULL DEFAULT '',
	parentObjectID INT(10) NOT NULL DEFAULT 0,
	objectType VARCHAR(255) NOT NULL DEFAULT '',
	objectID INT(10) NOT NULL DEFAULT 0,
	sessionVariables MEDIUMTEXT,
	spiderID INT(10) NOT NULL DEFAULT 0,
	KEY packageID (packageID, lastActivityTime, spiderID)
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
	disabled TINYINT(1) NOT NULL DEFAULT 0,
	styleDescription TEXT,
	styleVersion VARCHAR(255) NOT NULL DEFAULT '',
	styleDate CHAR(10) NOT NULL DEFAULT '0000-00-00',
	image VARCHAR(255) NOT NULL DEFAULT '',
	copyright VARCHAR(255) NOT NULL DEFAULT '',
	license VARCHAR(255) NOT NULL DEFAULT '',
	authorName VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	iconPath VARCHAR(255) NOT NULL DEFAULT '',
	imagePath VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_style_to_package;
CREATE TABLE wcf1_style_to_package (
	styleID INT(10) NOT NULL,
	packageID INT(10) NOT NULL,
	isDefault TINYINT(1) NOT NULL DEFAULT 0,
	disabled TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY (styleID, packageID)
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

DROP TABLE IF EXISTS wcf1_template;
CREATE TABLE wcf1_template (
	templateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	templateName VARCHAR(255) NOT NULL DEFAULT '',
	templateGroupID INT(10),
	obsolete TINYINT(1) NOT NULL DEFAULT 0,
	KEY packageID (packageID, templateName),
	KEY templateGroupID (packageID, templateGroupID, templateName)
);

DROP TABLE IF EXISTS wcf1_template_group;
CREATE TABLE wcf1_template_group (
	templateGroupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parentTemplateGroupID INT(10) NOT NULL DEFAULT 0,
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

DROP TABLE IF EXISTS wcf1_user;
CREATE TABLE wcf1_user (
	userID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(255) NOT NULL DEFAULT '',
	email VARCHAR(255) NOT NULL DEFAULT '',
	password VARCHAR(40) NOT NULL DEFAULT '',
	salt VARCHAR(40) NOT NULL DEFAULT '',
	accessToken CHAR(40) NOT NULL DEFAULT '',
	languageID INT(10) NOT NULL DEFAULT 0,
	registrationDate INT(10) NOT NULL DEFAULT 0,
	styleID INT(10) NOT NULL DEFAULT 0,
	
	KEY username (username),
	KEY registrationDate (registrationDate),
	KEY styleID (styleID)
);

DROP TABLE IF EXISTS wcf1_user_collapsible_content;
CREATE TABLE wcf1_user_collapsible_content (
	objectTypeID INT(10) NOT NULL,
	objectID VARCHAR(50) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY (objectTypeID, objectID, userID)
);

DROP TABLE IF EXISTS wcf1_user_group;
CREATE TABLE wcf1_user_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(255) NOT NULL DEFAULT '',
	groupType TINYINT(1) NOT NULL DEFAULT 4
);

DROP TABLE IF EXISTS wcf1_user_group_option;
CREATE TABLE wcf1_user_group_option  (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	optionName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(255) NOT NULL DEFAULT '',
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue MEDIUMTEXT,
	adminDefaultValue MEDIUMTEXT,
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
	UNIQUE KEY categoryName (categoryName, packageID)
);

DROP TABLE IF EXISTS wcf1_user_group_option_value;
CREATE TABLE wcf1_user_group_option_value  (
	groupID INT(10) NOT NULL,
	optionID INT(10) NOT NULL,
	optionValue MEDIUMTEXT NOT NULL,
	UNIQUE KEY groupID (groupID, optionID)
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
	disabled TINYINT(1) NOT NULL DEFAULT 0,
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
	categoryIconS VARCHAR(255) NOT NULL DEFAULT '',
	categoryIconM VARCHAR(255) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY categoryName (categoryName, packageID)
);

DROP TABLE IF EXISTS wcf1_user_option_value;
CREATE TABLE wcf1_user_option_value (
	userID INT(10) NOT NULL PRIMARY KEY
);

DROP TABLE IF EXISTS wcf1_user_storage;
CREATE TABLE wcf1_user_storage (
	userID INT(10) NOT NULL,
	field VARCHAR(80) NOT NULL DEFAULT '',
	fieldValue TEXT,
	packageID INT(10),
	UNIQUE KEY userStorageData (userID, field, packageID)
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

/* foreign keys */
ALTER TABLE wcf1_acp_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_search_provider ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (sessionLogID) REFERENCES wcf1_acp_session_log (sessionLogID) ON DELETE CASCADE;
ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_session_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_application ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_application ADD FOREIGN KEY (groupID) REFERENCES wcf1_application_group (groupID) ON DELETE SET NULL;

ALTER TABLE wcf1_category ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_cleanup_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_cleanup_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

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

ALTER TABLE wcf1_language_to_package ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_language_to_package ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_object_type ADD FOREIGN KEY (definitionID) REFERENCES wcf1_object_type_definition (definitionID) ON DELETE CASCADE;
ALTER TABLE wcf1_object_type ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_object_type_definition ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_dependency ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_dependency ADD FOREIGN KEY (dependency) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_exclusion ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_file_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_form ADD FOREIGN KEY (queueID) REFERENCES wcf1_package_installation_queue (queueID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_node ADD FOREIGN KEY (queueID) REFERENCES wcf1_package_installation_queue (queueID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_plugin ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_installation_queue ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_installation_queue ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_package_installation_sql_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_requirement ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_requirement ADD FOREIGN KEY (requirement) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_requirement_map ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_requirement_map ADD FOREIGN KEY (requirement) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update ADD FOREIGN KEY (packageUpdateServerID) REFERENCES wcf1_package_update_server (packageUpdateServerID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_exclusion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_fromversion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_requirement ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_version ADD FOREIGN KEY (packageUpdateID) REFERENCES wcf1_package_update (packageUpdateID) ON DELETE CASCADE;

ALTER TABLE wcf1_page_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_search ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_session ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_sitemap ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_storage ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_storage ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_to_package ADD FOREIGN KEY (styleID) REFERENCES wcf1_style (styleID) ON DELETE CASCADE;
ALTER TABLE wcf1_style_to_package ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_variable_value ADD FOREIGN KEY (styleID) REFERENCES wcf1_style (styleID) ON DELETE CASCADE;
ALTER TABLE wcf1_style_variable_value ADD FOREIGN KEY (variableID) REFERENCES wcf1_style_variable (variableID) ON DELETE CASCADE;

ALTER TABLE wcf1_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_template ADD FOREIGN KEY (templateGroupID) REFERENCES wcf1_template_group (templateGroupID) ON DELETE CASCADE;

ALTER TABLE wcf1_template_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_option_value ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_group_option_value ADD FOREIGN KEY (optionID) REFERENCES wcf1_user_group_option (optionID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_option_value ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_to_group ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_to_language ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_to_language ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

/* default inserts */
-- default user groups
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group1', 1);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group2', 2);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group3', 3);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group4', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group5', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('wcf.acp.group.group6', 4);

-- default user group options
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, adminDefaultValue, showOrder) VALUES ('admin.general.canUseAcp', 'admin.general', 'boolean', '0', '1', 1);
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, adminDefaultValue, showOrder) VALUES ('admin.system.package.canInstallPackage', 'admin.system.package', 'boolean', '0', '1', 1);
INSERT INTO wcf1_user_group_option (optionName, categoryName, optionType, defaultValue, adminDefaultValue, showOrder) VALUES ('admin.user.canEditGroup', 'admin.user.group', 'boolean', '0', '1', 1);

-- default user group option values
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 1, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 2, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 3, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 1, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 2, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 3, '1');	-- Administrators

-- default update servers
INSERT INTO wcf1_package_update_server (serverURL, status, disabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, disabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');

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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLabelColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeadlineColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeadlineFontFamily', '"Trebuchet MS", Arial, sans-serif');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackgroundColor', '@wcfContentBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownColor', '@wcfColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBorderColor', '@wcfContainerBorderColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownHoverBackgroundColor', '@wcfContainerHoverBackgroundColor');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfBaseLineHeight', '1.27');
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfMarkedBackgroundColor', 'rgba(255, 255, 200, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useFluidLayout', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogo', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('individualLess', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('overrideLess', '');
