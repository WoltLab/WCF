/**** tables ****/
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
	username VARCHAR(255) NOT NULL DEFAULT '',
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
	UNIQUE KEY actionName (packageID, actionName)
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

DROP TABLE IF EXISTS wcf1_page_location;
CREATE TABLE wcf1_page_location (
	locationID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	locationPattern VARCHAR(255) NOT NULL DEFAULT '',
	locationName VARCHAR(255) NOT NULL DEFAULT '',
	packageID INT(10) NOT NULL,
	className varchar(255) NOT NULL DEFAULT '',
	UNIQUE KEY (packageID, locationName)
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
	UNIQUE KEY (packageID, menuItem)
);

DROP TABLE IF EXISTS wcf1_route;
CREATE TABLE wcf1_route (
	routeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	routeName VARCHAR(255) NOT NULL,
	routeSchema VARCHAR(255) NOT NULL,
	controller VARCHAR(255),
	isACPRoute TINYINT(1) NOT NULL,
	partsPattern VARCHAR(255),
	UNIQUE KEY (packageID, routeName, isACPRoute)
);

DROP TABLE IF EXISTS wcf1_route_component;
CREATE TABLE wcf1_route_component (
	componentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	routeID INT(10) NOT NULL,
	componentName VARCHAR(255) NOT NULL,
	defaultValue VARCHAR(255),
	pattern VARCHAR(255),
	isOptional TINYINT(1) NOT NULL,
	UNIQUE KEY (routeID, componentName)
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
	username VARCHAR(255) NOT NULL DEFAULT '',
	sessionVariables MEDIUMTEXT,
	spiderID INT(10) NOT NULL DEFAULT 0,
	KEY packageID (packageID, lastActivityTime, spiderID)
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
	authorURL VARCHAR(255) NOT NULL DEFAULT ''
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
	styleID INT(10) NOT NULL,
	variableName VARCHAR(50) NOT NULL DEFAULT '',
	variableValue MEDIUMTEXT,
	UNIQUE KEY (styleID, variableName)
);

DROP TABLE IF EXISTS wcf1_style_variable_to_attribute;
CREATE TABLE wcf1_style_variable_to_attribute (
	packageID INT(10) NOT NULL,
	cssSelector VARCHAR(200) NOT NULL DEFAULT '',
	attributeName VARCHAR(50) NOT NULL DEFAULT '',
	variableName VARCHAR(50) NOT NULL DEFAULT '',
	UNIQUE KEY (packageID, cssSelector, attributeName, variableName)
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
	objectID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY (objectTypeID, objectID, userID)
);

DROP TABLE IF EXISTS wcf1_user_group;
CREATE TABLE wcf1_user_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(255) NOT NULL DEFAULT '',
	groupType TINYINT(1) NOT NULL DEFAULT 0
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

/**** foreign keys ****/
ALTER TABLE wcf1_acp_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (sessionLogID) REFERENCES wcf1_acp_session_log (sessionLogID) ON DELETE CASCADE;
ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_session_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_application ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_application ADD FOREIGN KEY (groupID) REFERENCES wcf1_application_group (groupID) ON DELETE SET NULL;

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

ALTER TABLE wcf1_page_location ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_page_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_route ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_route_component ADD FOREIGN KEY (routeID) REFERENCES wcf1_route (routeID) ON DELETE CASCADE;

ALTER TABLE wcf1_search ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_session ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_storage ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_storage ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_to_package ADD FOREIGN KEY (styleID) REFERENCES wcf1_style (styleID) ON DELETE CASCADE;
ALTER TABLE wcf1_style_to_package ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_variable ADD FOREIGN KEY (styleID) REFERENCES wcf1_style (styleID) ON DELETE CASCADE;

ALTER TABLE wcf1_style_variable_to_attribute ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

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

/**** default inserts ****/
-- default user groups
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Everyone', 1);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Guests', 2);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Users', 3);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Administrators', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Moderators', 4);
INSERT INTO wcf1_user_group (groupName, groupType) VALUES ('Super Moderators', 4);

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

-- default routes
INSERT INTO wcf1_route (routeName, routeSchema, controller, isACPRoute) VALUES ('com.woltlab.wcf.default', '/{controller}/{id}-{title}', NULL, 0);
INSERT INTO wcf1_route_component (routeID, componentName, defaultValue, isOptional) VALUES (1, 'controller', 'Index', 1);
INSERT INTO wcf1_route_component (routeID, componentName, pattern, isOptional) VALUES (1, 'id', '\\d+', 1);
INSERT INTO wcf1_route_component (routeID, componentName, pattern, isOptional) VALUES (1, 'title', '\\w+', 1);

INSERT INTO wcf1_route (routeName, routeSchema, controller, isACPRoute) VALUES ('com.woltlab.wcf.acp.default', '/{controller}/{id}-{title}', NULL, 1);
INSERT INTO wcf1_route_component (routeID, componentName, defaultValue, isOptional) VALUES (2, 'controller', 'Index', 1);
INSERT INTO wcf1_route_component (routeID, componentName, pattern, isOptional) VALUES (2, 'id', '\\d+', 1);
INSERT INTO wcf1_route_component (routeID, componentName, pattern, isOptional) VALUES (2, 'title', '\\w+', 1);

-- default update servers
INSERT INTO wcf1_package_update_server (serverURL, status, disabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, disabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/maelstrom/', 'online', 0, NULL, 0, '', '');
