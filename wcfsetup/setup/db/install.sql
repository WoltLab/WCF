/*
	This table was moved up here, because it must be created during the first iteration
	
	DO *NOT* MOVE IT BACK!
*/
DROP TABLE IF EXISTS wcf1_package_installation_sql_log;
CREATE TABLE wcf1_package_installation_sql_log ( 
	packageID INT(10), 
	sqlTable VARCHAR(100) NOT NULL DEFAULT '', 
	sqlColumn VARCHAR(100) NOT NULL DEFAULT '', 
	sqlIndex VARCHAR(100) NOT NULL DEFAULT '',
	isDone TINYINT(1) NOT NULL DEFAULT 1,
	UNIQUE KEY packageID (packageID, sqlTable, sqlColumn, sqlIndex) 
);

/* tables */
DROP TABLE IF EXISTS wcf1_acl_option;
CREATE TABLE wcf1_acl_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	optionName VARCHAR(191) NOT NULL,
	categoryName VARCHAR(191) NOT NULL,
	UNIQUE KEY (packageID, objectTypeID, optionName)
);

DROP TABLE IF EXISTS wcf1_acl_option_category;
CREATE TABLE wcf1_acl_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	categoryName VARCHAR(191) NOT NULL,
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

DROP TABLE IF EXISTS wcf1_acp_menu_item;
CREATE TABLE wcf1_acp_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(191) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(191) NOT NULL DEFAULT '',
	menuItemController VARCHAR(255) NOT NULL DEFAULT '',
	menuItemLink VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	icon VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY menuItem (menuItem, packageID)
);

DROP TABLE IF EXISTS wcf1_acp_search_provider;
CREATE TABLE wcf1_acp_search_provider (
	providerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	providerName VARCHAR(191) NOT NULL DEFAULT '',
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

DROP TABLE IF EXISTS wcf1_acp_session_virtual;
CREATE TABLE wcf1_acp_session_virtual (
	virtualSessionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID CHAR(40) NOT NULL,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(191) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (sessionID, ipAddress, userAgent)
);

DROP TABLE IF EXISTS wcf1_acp_template;
CREATE TABLE wcf1_acp_template (
	templateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	templateName VARCHAR(191) NOT NULL,
	application VARCHAR(20) NOT NULL,
	UNIQUE KEY applicationTemplate (application, templateName)
);

DROP TABLE IF EXISTS wcf1_ad;
CREATE TABLE wcf1_ad (
	adID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	adName VARCHAR(255) NOT NULL,
	ad MEDIUMTEXT,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_application;
CREATE TABLE wcf1_application (
	packageID INT(10) NOT NULL PRIMARY KEY,
	domainName VARCHAR(255) NOT NULL,
	domainPath VARCHAR(255) NOT NULL DEFAULT '/',
	cookieDomain VARCHAR(255) NOT NULL,
	isTainted TINYINT(1) NOT NULL DEFAULT 0,
	landingPageID INT(10) NULL
);

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
	isDeleted TINYINT(1) NOT NULL DEFAULT 0,
	hasLabels TINYINT(1) NOT NULL DEFAULT 0,
	
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
	teaserImageID INT(10),
	hasEmbeddedObjects TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (articleID, languageID)
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

DROP TABLE IF EXISTS wcf1_background_job;
CREATE TABLE wcf1_background_job (
	jobID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	job MEDIUMBLOB NOT NULL,
	status ENUM('ready', 'processing') NOT NULL DEFAULT 'ready',
	time INT(10) NOT NULL,
	KEY (status, time)
);

DROP TABLE IF EXISTS wcf1_bbcode;
CREATE TABLE wcf1_bbcode (
	bbcodeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	bbcodeTag VARCHAR(191) NOT NULL,
	packageID INT(10) NOT NULL,
	htmlOpen VARCHAR(255) NOT NULL DEFAULT '',
	htmlClose VARCHAR(255) NOT NULL DEFAULT '',
	className VARCHAR(255) NOT NULL DEFAULT '',
	wysiwygIcon varchar(255) NOT NULL DEFAULT '',
	buttonLabel VARCHAR(255) NOT NULL DEFAULT '',
	isBlockElement TINYINT(1) NOT NULL DEFAULT 0,
	isSourceCode TINYINT(1) NOT NULL DEFAULT 0,
	showButton TINYINT(1) NOT NULL DEFAULT 0,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
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
	name VARCHAR(80) NOT NULL,
	packageID INT(10) NOT NULL,
	title VARCHAR(255) NOT NULL,
	regex TEXT NOT NULL,
	html TEXT NOT NULL,
	className varchar(255) NOT NULL DEFAULT '',
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY name (name, packageID)
);

DROP TABLE IF EXISTS wcf1_blacklist_status;
CREATE TABLE wcf1_blacklist_status (
	date DATE NOT NULL,
	delta1 TINYINT(1) NOT NULL DEFAULT 0,
	delta2 TINYINT(1) NOT NULL DEFAULT 0,
	delta3 TINYINT(1) NOT NULL DEFAULT 0,
	delta4 TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY day (date)
);

DROP TABLE IF EXISTS wcf1_blacklist_entry;
CREATE TABLE wcf1_blacklist_entry (
	type ENUM('email', 'ipv4','ipv6','username'),
	hash BINARY(32),
	lastSeen DATETIME NOT NULL,
	occurrences SMALLINT(5) NOT NULL,
	
	UNIQUE KEY entry (type, hash),
	KEY numberOfReports (type, occurrences)
);

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
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	showHeader TINYINT(1) NOT NULL DEFAULT 1,
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	packageID INT(10) NOT NULL,
	menuID INT(10) NULL,
	linkPageID INT(10),
	linkPageObjectID INT(10) NOT NULL DEFAULT 0,
	externalURL VARCHAR(255) NOT NULL DEFAULT '',
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
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

DROP TABLE IF EXISTS wcf1_captcha_question;
CREATE TABLE wcf1_captcha_question (
	questionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	question VARCHAR(255) NOT NULL,
	answers MEDIUMTEXT,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_category;
CREATE TABLE wcf1_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	parentCategoryID INT(10) NOT NULL DEFAULT 0,
	title VARCHAR(255) NOT NULL,
	description TEXT,
	descriptionUseHtml TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0,
	time INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	additionalData TEXT
);

DROP TABLE IF EXISTS wcf1_cli_history;
CREATE TABLE wcf1_cli_history (
	historyItem INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	command VARCHAR(255) NOT NULL,
	KEY (userID)
);

DROP TABLE IF EXISTS wcf1_clipboard_action;
CREATE TABLE wcf1_clipboard_action (
	actionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL DEFAULT 0,
	actionName VARCHAR(50) NOT NULL DEFAULT '',
	actionClassName VARCHAR(191) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY actionName (packageID, actionName, actionClassName)
);

DROP TABLE IF EXISTS wcf1_clipboard_item;
CREATE TABLE wcf1_clipboard_item (
	objectTypeID INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NOT NULL DEFAULT 0,
	objectID INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (objectTypeID, userID, objectID),
	KEY (userID)
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
	message MEDIUMTEXT NOT NULL,
	responses MEDIUMINT(7) NOT NULL DEFAULT '0',
	responseIDs VARCHAR(255) NOT NULL DEFAULT '',
	unfilteredResponses MEDIUMINT(7) NOT NULL DEFAULT '0',
	unfilteredResponseIDs VARCHAR(255) NOT NULL DEFAULT '',
	enableHtml TINYINT(1) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	
	KEY (objectTypeID, objectID, isDisabled, time),
	KEY lastCommentTime (userID, time)
);

DROP TABLE IF EXISTS wcf1_comment_response;
CREATE TABLE wcf1_comment_response (
	responseID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	commentID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	userID INT(10),
	username VARCHAR(255) NOT NULL,
	message MEDIUMTEXT NOT NULL,
	enableHtml TINYINT(1) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	
	KEY (commentID, isDisabled, time),
	KEY lastResponseTime (userID, time)
);

DROP TABLE IF EXISTS wcf1_condition;
CREATE TABLE wcf1_condition (
	conditionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	conditionData MEDIUMTEXT
);

DROP TABLE IF EXISTS wcf1_contact_attachment;
CREATE TABLE wcf1_contact_attachment (
	attachmentID INT(10) NOT NULL,
	accessKey CHAR(40) NOT NULL
);

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

/* SQL_PARSER_OFFSET */

DROP TABLE IF EXISTS wcf1_core_object;
CREATE TABLE wcf1_core_object (
	objectID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectName VARCHAR(191) NOT NULL DEFAULT '',
	UNIQUE KEY object (packageID, objectName)
);

DROP TABLE IF EXISTS wcf1_cronjob;
CREATE TABLE wcf1_cronjob (
	cronjobID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	className varchar(255) NOT NULL DEFAULT '',
	packageID INT(10) NOT NULL,
	cronjobName VARCHAR(191) NOT NULL,
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
	failCount TINYINT(1) NOT NULL DEFAULT 0,
	options TEXT,
	
	UNIQUE KEY cronjobName (cronjobName, packageID)
);

DROP TABLE IF EXISTS wcf1_cronjob_log;
CREATE TABLE wcf1_cronjob_log (
	cronjobLogID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	cronjobID INT(10) NOT NULL,
	execTime INT(10) NOT NULL DEFAULT 0,
	success TINYINT(1) NOT NULL DEFAULT 0,
	error TEXT
);

DROP TABLE IF EXISTS wcf1_devtools_project;
CREATE TABLE wcf1_devtools_project (
	projectID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(191) NOT NULL,
	path TEXT,
	
	UNIQUE KEY name (name)
);

DROP TABLE IF EXISTS wcf1_devtools_missing_language_item;
CREATE TABLE wcf1_devtools_missing_language_item (
	itemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageID INT(10),
	languageItem VARCHAR(191) NOT NULL,
	lastTime INT(10) NOT NULL,
	stackTrace MEDIUMTEXT NOT NULL,
	
	UNIQUE KEY (languageID, languageItem)
);

DROP TABLE IF EXISTS wcf1_edit_history_entry;
CREATE TABLE wcf1_edit_history_entry (
	entryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	obsoletedAt INT(10) NOT NULL DEFAULT 0,
	obsoletedByUserID INT(10),
	message MEDIUMTEXT,
	editReason TEXT,
	
	KEY (objectTypeID, objectID),
	KEY (obsoletedAt, obsoletedByUserID)
);

DROP TABLE IF EXISTS wcf1_event_listener;
CREATE TABLE wcf1_event_listener (
	listenerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	environment ENUM('user', 'admin') NOT NULL DEFAULT 'user',
	listenerName VARCHAR(191) NOT NULL,
	eventClassName VARCHAR(255) NOT NULL DEFAULT '',
	eventName TEXT,
	listenerClassName VARCHAR(200) NOT NULL DEFAULT '',
	inherit TINYINT(1) NOT NULL DEFAULT 0,
	niceValue TINYINT(3) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	
	UNIQUE KEY listenerName (listenerName, packageID)
);

DROP TABLE IF EXISTS wcf1_import_mapping;
CREATE TABLE wcf1_import_mapping (
	importHash CHAR(8) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	oldID VARCHAR(191) NOT NULL,
	newID INT(10) NOT NULL,
	UNIQUE KEY (importHash, objectTypeID, oldID)
);

DROP TABLE IF EXISTS wcf1_label;
CREATE TABLE wcf1_label (
	labelID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10) NOT NULL,
	label VARCHAR(80) NOT NULL,
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_label_group;
CREATE TABLE wcf1_label_group (
	groupID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupName VARCHAR(80) NOT NULL,
	groupDescription VARCHAR(255) NOT NULL DEFAULT '',
	forceSelection TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0
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
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY languageCode (languageCode)
);

DROP TABLE IF EXISTS wcf1_language_category;
CREATE TABLE wcf1_language_category (
	languageCategoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageCategory VARCHAR(191) NOT NULL DEFAULT '',
	UNIQUE KEY languageCategory (languageCategory)
);

DROP TABLE IF EXISTS wcf1_language_item;
CREATE TABLE wcf1_language_item (
	languageItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageID INT(10) NOT NULL,
	languageItem VARCHAR(191) NOT NULL DEFAULT '',
	languageItemValue MEDIUMTEXT NOT NULL,
	languageCustomItemValue MEDIUMTEXT,
	languageUseCustomValue TINYINT(1) NOT NULL DEFAULT 0,
	languageItemOriginIsSystem TINYINT(1) NOT NULL DEFAULT 1,
	languageCategoryID INT(10) NOT NULL,
	packageID INT(10),
	languageItemOldValue MEDIUMTEXT,
	languageCustomItemDisableTime INT(10),
	isCustomLanguageItem TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY languageItem (languageItem, languageID),
	KEY languageItemOriginIsSystem (languageItemOriginIsSystem)
);

DROP TABLE IF EXISTS wcf1_like;
CREATE TABLE wcf1_like (
	likeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	objectID INT(10) NOT NULL DEFAULT 0,
	objectTypeID INT(10) NOT NULL,
	objectUserID INT(10),
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 1,
	likeValue TINYINT(1) NOT NULL DEFAULT 1,
	reactionTypeID INT(10) NOT NULL,
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
	cachedReactions TEXT,
	UNIQUE KEY (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_media;
CREATE TABLE wcf1_media (
	mediaID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	categoryID INT(10),
	
	filename VARCHAR(255) NOT NULL DEFAULT '',
	filesize INT(10) NOT NULL DEFAULT 0,
	fileType VARCHAR(255) NOT NULL DEFAULT '',
	fileHash VARCHAR(255) NOT NULL DEFAULT '',
	uploadTime INT(10) NOT NULL DEFAULT 0,
	fileUpdateTime INT(10) NOT NULL DEFAULT 0,
	userID INT(10),
	username VARCHAR(255) NOT NULL,
	languageID INT(10),
	isMultilingual TINYINT(1) NOT NULL DEFAULT 0,
	captionEnableHtml TINYINT(1) NOT NULL DEFAULT 0,
	
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
	largeThumbnailHeight SMALLINT(5) NOT NULL DEFAULT 0,
	
	downloads INT(10) NOT NULL DEFAULT 0,
	lastDownloadTime INT(10) NOT NULL DEFAULT 0
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

DROP TABLE IF EXISTS wcf1_message_embedded_object;
CREATE TABLE wcf1_message_embedded_object (
	messageObjectTypeID INT(10) NOT NULL,
	messageID INT(10) NOT NULL,
	embeddedObjectTypeID INT(10) NOT NULL,
	embeddedObjectID INT(10) NOT NULL,
	
	KEY (messageObjectTypeID, messageID)
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
	comments SMALLINT(5) NOT NULL DEFAULT 0,
	lastChangeTime INT(10) NOT NULL DEFAULT 0,
	
	-- additional data, e.g. message if reporting content
	additionalData TEXT,
	
	KEY objectTypeAndID (objectTypeID, objectID)
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
	parentObjectID INT(10),
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	action VARCHAR(80) NOT NULL,
	hidden TINYINT(1) NOT NULL DEFAULT 1,
	additionalData MEDIUMTEXT,
	
	KEY objectTypeAndID (objectTypeID, objectID)
);

DROP TABLE IF EXISTS wcf1_notice;
CREATE TABLE wcf1_notice (
	noticeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	noticeName VARCHAR(255) NOT NULL,
	notice MEDIUMTEXT,
	noticeUseHtml TINYINT(1) NOT NULL DEFAULT 0,
	cssClassName VARCHAR(255) NOT NULL DEFAULT 'info',
	showOrder INT(10) NOT NULL DEFAULT 0,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	isDismissible TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_notice_dismissed;
CREATE TABLE wcf1_notice_dismissed (
	noticeID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	PRIMARY KEY (noticeID, userID)
);

DROP TABLE IF EXISTS wcf1_object_type;
CREATE TABLE wcf1_object_type (
	objectTypeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	definitionID INT(10) NOT NULL,
	packageID INT(10) NOT NULL,
	objectType VARCHAR(191) NOT NULL,
	className VARCHAR(255) NOT NULL DEFAULT '',
	additionalData MEDIUMTEXT,
	UNIQUE KEY objectType (objectType, definitionID, packageID)
);

DROP TABLE IF EXISTS wcf1_object_type_definition;
CREATE TABLE wcf1_object_type_definition (
	definitionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	definitionName VARCHAR(191) NOT NULL,
	packageID INT(10) NOT NULL,
	interfaceName VARCHAR(255) NOT NULL DEFAULT '',
	categoryName VARCHAR(80) NOT NULL DEFAULT '',
	UNIQUE KEY definitionName (definitionName)
);

DROP TABLE IF EXISTS wcf1_option;
CREATE TABLE wcf1_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	optionName VARCHAR(191) NOT NULL DEFAULT '',
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
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
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(191) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_package;
CREATE TABLE wcf1_package (
	packageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	package VARCHAR(191) NOT NULL DEFAULT '',
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
	UNIQUE KEY package (package)
);

DROP TABLE IF EXISTS wcf1_package_compatibility;
CREATE TABLE wcf1_package_compatibility (
	packageID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageID, version)
);

DROP TABLE IF EXISTS wcf1_package_exclusion;
CREATE TABLE wcf1_package_exclusion (
	packageID INT(10) NOT NULL,
	excludedPackage VARCHAR(191) NOT NULL DEFAULT '',
	excludedPackageVersion VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY packageID (packageID, excludedPackage)
);

DROP TABLE IF EXISTS wcf1_package_installation_file_log;
CREATE TABLE wcf1_package_installation_file_log (
	packageID INT(10),
	filename VARBINARY(765) NOT NULL, -- VARBINARY(765) roughly equals VARCHAR(255)
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
	pluginName VARCHAR(191) NOT NULL PRIMARY KEY,
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
	isApplication TINYINT(1) NOT NULL DEFAULT 0
);

/* The table `wcf1_package_installation_sql_log` can be found at the very top! */

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
	package VARCHAR(191) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	packageDescription VARCHAR(255) NOT NULL DEFAULT '',
	author VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	isApplication TINYINT(1) NOT NULL DEFAULT 0,
	pluginStoreFileID INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY packageUpdateServerID (packageUpdateServerID, package)
);

DROP TABLE IF EXISTS wcf1_package_update_compatibility;
CREATE TABLE wcf1_package_update_compatibility (
	packageUpdateVersionID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageUpdateVersionID, version)
);

DROP TABLE IF EXISTS wcf1_package_update_exclusion;
CREATE TABLE wcf1_package_update_exclusion (
	packageUpdateVersionID INT(10) NOT NULL,
	excludedPackage VARCHAR(191) NOT NULL DEFAULT '',
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
	package VARCHAR(191) NOT NULL DEFAULT '',
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
	errorMessage TEXT,
	apiVersion ENUM('2.0', '2.1', '3.1') NOT NULL DEFAULT '2.0',
	metaData TEXT
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
	UNIQUE KEY packageUpdateID (packageUpdateID, packageVersion)
);

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
	overrideApplicationPackageID INT(10),
	controller VARCHAR(255) NOT NULL DEFAULT '',
	handler VARCHAR(255) NOT NULL DEFAULT '',
	controllerCustomURL VARCHAR(255) NOT NULL DEFAULT '',
	requireObjectID TINYINT(1) NOT NULL DEFAULT 0,
	hasFixedParent TINYINT(1) NOT NULL DEFAULT 0,
	lastUpdateTime INT(10) NOT NULL DEFAULT 0,
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	availableDuringOfflineMode TINYINT(1) NOT NULL DEFAULT 0,
	allowSpidersToIndex TINYINT(1) NOT NULL DEFAULT 0,
	excludeFromLandingPage TINYINT(1) NOT NULL DEFAULT 0,
	enableShareButtons TINYINT(1) NOT NULL DEFAULT 0,
	permissions TEXT NULL,
	options TEXT NULL
);

DROP TABLE IF EXISTS wcf1_page_box_order;
CREATE TABLE wcf1_page_box_order (
	pageID INT(10) NOT NULL,
	boxID INT(10) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY pageToBox (pageID, boxID)
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

DROP TABLE IF EXISTS wcf1_paid_subscription;
CREATE TABLE wcf1_paid_subscription (
	subscriptionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(255) NOT NULL DEFAULT '',
	description TEXT,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0,
	cost DECIMAL(10,2) NOT NULL DEFAULT 0,
	currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
	subscriptionLength SMALLINT(3) NOT NULL DEFAULT 0,
	subscriptionLengthUnit ENUM('', 'D', 'M', 'Y') NOT NULL DEFAULT '',
	isRecurring TINYINT(1) NOT NULL DEFAULT 0,
	groupIDs TEXT,
	excludedSubscriptionIDs TEXT
);

DROP TABLE IF EXISTS wcf1_paid_subscription_user;
CREATE TABLE wcf1_paid_subscription_user (
	subscriptionUserID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	subscriptionID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	startDate INT(10) NOT NULL DEFAULT 0,
	endDate INT(10) NOT NULL DEFAULT 0,
	isActive TINYINT(1) NOT NULL DEFAULT 1,
	sentExpirationNotification TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (subscriptionID, userID),
	KEY (isActive)
);

DROP TABLE IF EXISTS wcf1_paid_subscription_transaction_log;
CREATE TABLE wcf1_paid_subscription_transaction_log (
	logID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	subscriptionUserID INT(10),
	userID INT(10),
	subscriptionID INT(10),
	paymentMethodObjectTypeID INT(10) NOT NULL,
	logTime INT(10) NOT NULL DEFAULT 0,
	transactionID VARCHAR(255) NOT NULL DEFAULT '',
	transactionDetails MEDIUMTEXT,
	logMessage VARCHAR(255) NOT NULL DEFAULT ''
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

DROP TABLE IF EXISTS wcf1_reaction_type; 
CREATE TABLE wcf1_reaction_type (
	reactionTypeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	title VARCHAR(255) NOT NULL, 
	showOrder INT(10) NOT NULL DEFAULT 0,
	iconFile VARCHAR(255) NOT NULL DEFAULT '', 
	isAssignable TINYINT(1) NOT NULL DEFAULT 1
);

DROP TABLE IF EXISTS wcf1_registry;
CREATE TABLE wcf1_registry (
	packageID INT(10) NOT NULL,
	field VARCHAR(191) NOT NULL,
	fieldValue MEDIUMTEXT,
	
	UNIQUE KEY uniqueField (packageID, field)
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

DROP TABLE IF EXISTS wcf1_search_keyword;
CREATE TABLE wcf1_search_keyword (
	keywordID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	keyword VARCHAR(191) NOT NULL,
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
	userAgent VARCHAR(191) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	requestURI VARCHAR(255) NOT NULL DEFAULT '',
	requestMethod VARCHAR(7) NOT NULL DEFAULT '',
	pageID INT(10),
	pageObjectID INT(10),
	parentPageID INT(10),
	parentPageObjectID INT(10),
	spiderID INT(10),
	sessionVariables MEDIUMTEXT,
	KEY packageID (lastActivityTime, spiderID),
	KEY pageID (pageID, pageObjectID),
	KEY parentPageID (parentPageID, parentPageObjectID),
	UNIQUE KEY uniqueUserID (userID)
);

DROP TABLE IF EXISTS wcf1_session_virtual;
CREATE TABLE wcf1_session_virtual (
	virtualSessionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID CHAR(40) NOT NULL,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(191) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (sessionID, ipAddress, userAgent)
);

DROP TABLE IF EXISTS wcf1_smiley;
CREATE TABLE wcf1_smiley (
	smileyID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryID INT(10),
	smileyPath VARCHAR(255) NOT NULL DEFAULT '',
	smileyPath2x VARCHAR(255) NOT NULL DEFAULT '',
	smileyTitle VARCHAR(255) NOT NULL DEFAULT '',
	smileyCode VARCHAR(191) NOT NULL DEFAULT '',
	aliases TEXT NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY smileyCode (smileyCode)
);

DROP TABLE IF EXISTS wcf1_spider;
CREATE TABLE wcf1_spider (
	spiderID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	spiderIdentifier VARCHAR(191) DEFAULT '',
	spiderName VARCHAR(255) DEFAULT '',
	spiderURL VARCHAR(255) DEFAULT '',
	UNIQUE KEY spiderIdentifier (spiderIdentifier)
);

DROP TABLE IF EXISTS wcf1_stat_daily;
CREATE TABLE wcf1_stat_daily (
	statID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	date DATE NOT NULL,
	counter INT(10) NOT NULL DEFAULT 0,
	total INT(10) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (objectTypeID, date)
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
	image2x VARCHAR(255) NOT NULL DEFAULT '',
	copyright VARCHAR(255) NOT NULL DEFAULT '',
	license VARCHAR(255) NOT NULL DEFAULT '',
	authorName VARCHAR(255) NOT NULL DEFAULT '',
	authorURL VARCHAR(255) NOT NULL DEFAULT '',
	imagePath VARCHAR(255) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	isTainted TINYINT(1) NOT NULL DEFAULT 0,
	hasFavicon TINYINT(1) NOT NULL DEFAULT 0,
	coverPhotoExtension VARCHAR(4) NOT NULL DEFAULT '',
	apiVersion ENUM('3.0', '3.1', '5.2') NOT NULL DEFAULT '3.0' 
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
	name VARCHAR(191) NOT NULL,
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
	templateName VARCHAR(191) NOT NULL,
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
	niceValue TINYINT(3) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	
	KEY templateName (environment, templateName)
);

/* SQL_PARSER_OFFSET */

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
	revokeAutomatically TINYINT(1) NOT NULL DEFAULT 0,
	trophyUseHtml TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0,
	KEY(categoryID)
);

DROP TABLE IF EXISTS wcf1_user;
CREATE TABLE wcf1_user (
	userID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(100) NOT NULL DEFAULT '',
	email VARCHAR(191) NOT NULL DEFAULT '',
	password VARCHAR(100) NOT NULL DEFAULT '',
	accessToken CHAR(40) NOT NULL DEFAULT '',
	languageID INT(10) NOT NULL DEFAULT 0,
	registrationDate INT(10) NOT NULL DEFAULT 0,
	styleID INT(10) NOT NULL DEFAULT 0,
	banned TINYINT(1) NOT NULL DEFAULT 0,
	banReason MEDIUMTEXT NULL,
	banExpires INT(10) NOT NULL DEFAULT 0,
	activationCode INT(10) NOT NULL DEFAULT 0,
	emailConfirmed CHAR(40) DEFAULT NULL,
	lastLostPasswordRequestTime INT(10) NOT NULL DEFAULT 0,
	lostPasswordKey CHAR(40) DEFAULT NULL,
	lastUsernameChange INT(10) NOT NULL DEFAULT 0,
	newEmail VARCHAR(255) NOT NULL DEFAULT '',
	oldUsername VARCHAR(255) NOT NULL DEFAULT '',
	quitStarted INT(10) NOT NULL DEFAULT 0,
	reactivationCode INT(10) NOT NULL DEFAULT 0,
	registrationIpAddress VARCHAR(39) NOT NULL DEFAULT '',
	avatarID INT(10),
	disableAvatar TINYINT(1) NOT NULL DEFAULT 0,
	disableAvatarReason TEXT,
	disableAvatarExpires INT(10) NOT NULL DEFAULT 0,
	enableGravatar TINYINT(1) NOT NULL DEFAULT 0,
	gravatarFileExtension VARCHAR(3) NOT NULL DEFAULT '',
	signature TEXT,
	signatureEnableHtml TINYINT(1) NOT NULL DEFAULT 0,
	disableSignature TINYINT(1) NOT NULL DEFAULT 0,
	disableSignatureReason TEXT,
	disableSignatureExpires INT(10) NOT NULL DEFAULT 0,
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	profileHits INT(10) NOT NULL DEFAULT 0,
	rankID INT(10),
	userTitle VARCHAR(255) NOT NULL DEFAULT '',
	userOnlineGroupID INT(10),
	activityPoints INT(10) NOT NULL DEFAULT 0,
	notificationMailToken VARCHAR(20) NOT NULL DEFAULT '',
	authData VARCHAR(191) NOT NULL DEFAULT '',
	likesReceived MEDIUMINT(7) NOT NULL DEFAULT 0,
	trophyPoints INT(10) NOT NULL DEFAULT 0,
	coverPhotoHash CHAR(40) DEFAULT NULL,
	coverPhotoExtension VARCHAR(4) NOT NULL DEFAULT '',
	disableCoverPhoto TINYINT(1) NOT NULL DEFAULT 0,
	disableCoverPhotoReason TEXT,
	disableCoverPhotoExpires INT(10) NOT NULL DEFAULT 0,
	articles INT(10) NOT NULL DEFAULT 0,
	blacklistMatches VARCHAR(255) NOT NULL DEFAULT '',
	
	KEY username (username),
	KEY email (email),
	KEY registrationDate (registrationDate),
	KEY styleID (styleID),
	KEY activationCode (activationCode),
	KEY registrationData (registrationIpAddress, registrationDate),
	KEY activityPoints (activityPoints),
	KEY likesReceived (likesReceived),
	KEY authData (authData),
	KEY trophyPoints (trophyPoints)
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

DROP TABLE IF EXISTS wcf1_user_authentication_failure;
CREATE TABLE wcf1_user_authentication_failure (
	failureID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	environment ENUM('user', 'admin') NOT NULL DEFAULT 'user',
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	KEY (ipAddress, time),
	KEY (time)
);

DROP TABLE IF EXISTS wcf1_user_avatar;
CREATE TABLE wcf1_user_avatar (
	avatarID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	avatarName VARCHAR(255) NOT NULL DEFAULT '',
	avatarExtension VARCHAR(7) NOT NULL DEFAULT '',
	width SMALLINT(5) NOT NULL DEFAULT 0,
	height SMALLINT(5) NOT NULL DEFAULT 0,
	userID INT(10),
	fileHash VARCHAR(40) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS wcf1_user_collapsible_content;
CREATE TABLE wcf1_user_collapsible_content (
	objectTypeID INT(10) NOT NULL,
	objectID VARCHAR(191) NOT NULL,
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
	groupDescription TEXT,
	groupType TINYINT(1) NOT NULL DEFAULT 4,
	priority MEDIUMINT(8) NOT NULL DEFAULT 0,
	userOnlineMarking VARCHAR(255) NOT NULL DEFAULT '%s',
	showOnTeamPage TINYINT(1) NOT NULL DEFAULT 0,
	allowMention TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_group_assignment;
CREATE TABLE wcf1_user_group_assignment (
	assignmentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10) NOT NULL,
	title VARCHAR(255) NOT NULL,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_group_option;
CREATE TABLE wcf1_user_group_option (
	optionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10),
	optionName VARCHAR(191) NOT NULL DEFAULT '',
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
	optionType VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue MEDIUMTEXT,
	validationPattern TEXT,
	enableOptions MEDIUMTEXT,
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	usersOnly TINYINT(1) NOT NULL DEFAULT 0,
	additionalData MEDIUMTEXT,
	UNIQUE KEY optionName (optionName, packageID)
);

DROP TABLE IF EXISTS wcf1_user_group_option_category;
CREATE TABLE wcf1_user_group_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(191) NOT NULL DEFAULT '',
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
	trophyUseHtml TINYINT(1) NOT NULL DEFAULT 0,
	KEY(trophyID, time)
);

DROP TABLE IF EXISTS wcf1_user_menu_item;
CREATE TABLE wcf1_user_menu_item (
	menuItemID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	menuItem VARCHAR(191) NOT NULL DEFAULT '',
	parentMenuItem VARCHAR(191) NOT NULL DEFAULT '',
	menuItemController VARCHAR(255) NOT NULL DEFAULT '',
	menuItemLink VARCHAR(255) NOT NULL DEFAULT '',
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT,
	options TEXT,
	className VARCHAR(255) NOT NULL DEFAULT '',
	iconClassName VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY menuItem (menuItem, packageID)
);

-- notifications
DROP TABLE IF EXISTS wcf1_user_notification;
CREATE TABLE wcf1_user_notification (
	notificationID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	eventID INT(10) NOT NULL,
	objectID INT(10) NOT NULL DEFAULT 0,
	baseObjectID INT(10) NOT NULL DEFAULT 0,
	eventHash VARCHAR(40) NOT NULL DEFAULT '',
	authorID INT(10) NULL,
	timesTriggered INT(10) NOT NULL DEFAULT 0,
	guestTimesTriggered INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	mailNotified TINYINT(1) NOT NULL DEFAULT 0,
	confirmTime INT(10) NOT NULL DEFAULT 0,
	additionalData TEXT,
	KEY (userID, eventID, objectID, confirmTime),
	KEY (userID, confirmTime),
	KEY (confirmTime)
);

-- notification authors (stacking)
DROP TABLE IF EXISTS wcf1_user_notification_author;
CREATE TABLE wcf1_user_notification_author (
	notificationID INT(10) NOT NULL,
	authorID INT(10),
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (notificationID, authorID)
);

-- notification recipients
-- DEPRECATED
DROP TABLE IF EXISTS wcf1_user_notification_to_user;
CREATE TABLE wcf1_user_notification_to_user (
	notificationID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	UNIQUE KEY notificationID (notificationID, userID)
);

-- events that create notifications
DROP TABLE IF EXISTS wcf1_user_notification_event;
CREATE TABLE wcf1_user_notification_event (
	eventID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	eventName VARCHAR(191) NOT NULL DEFAULT '',
	objectTypeID INT(10) NOT NULL,
	className VARCHAR(255) NOT NULL DEFAULT '',
	permissions TEXT,
	options TEXT,
	preset TINYINT(1) NOT NULL DEFAULT 0,
	presetMailNotificationType ENUM('none', 'instant', 'daily') NOT NULL DEFAULT 'none',
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
	optionName VARCHAR(191) NOT NULL DEFAULT '',
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
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
	originIsSystem TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY optionName (optionName, packageID),
	KEY categoryName (categoryName)
);

DROP TABLE IF EXISTS wcf1_user_option_category;
CREATE TABLE wcf1_user_option_category (
	categoryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	categoryName VARCHAR(191) NOT NULL DEFAULT '',
	parentCategoryName VARCHAR(191) NOT NULL DEFAULT '',
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
	menuItem VARCHAR(191) NOT NULL,
	showOrder INT(10) NOT NULL DEFAULT 0,
	permissions TEXT NULL,
	options TEXT NULL,
	className VARCHAR(255) NOT NULL,
	UNIQUE KEY (packageID, menuItem)
);

DROP TABLE IF EXISTS wcf1_user_profile_visitor;
CREATE TABLE wcf1_user_profile_visitor (
	visitorID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	ownerID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (ownerID, userID),
	KEY (time)
);

DROP TABLE IF EXISTS wcf1_user_rank;
CREATE TABLE wcf1_user_rank (
	rankID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10) NOT NULL,
	requiredPoints INT(10) NOT NULL DEFAULT 0,
	rankTitle VARCHAR(255) NOT NULL DEFAULT '',
	cssClassName VARCHAR(255) NOT NULL DEFAULT '',
	rankImage VARCHAR(255) NOT NULL DEFAULT '',
	repeatImage TINYINT(3) NOT NULL DEFAULT 1,
	requiredGender TINYINT(1) NOT NULL DEFAULT 0,
	hideTitle TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_storage;
CREATE TABLE wcf1_user_storage (
	userID INT(10) NOT NULL,
	field VARCHAR(80) NOT NULL DEFAULT '',
	fieldValue MEDIUMTEXT,
	UNIQUE KEY (userID, field),
	KEY (field)
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

ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_search_provider ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_access_log ADD FOREIGN KEY (sessionLogID) REFERENCES wcf1_acp_session_log (sessionLogID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_acp_session_virtual ADD FOREIGN KEY (sessionID) REFERENCES wcf1_acp_session (sessionID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wcf1_acp_template ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_ad ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_application ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_application ADD FOREIGN KEY (landingPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

ALTER TABLE wcf1_article ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_article ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE wcf1_article_content ADD FOREIGN KEY (articleID) REFERENCES wcf1_article (articleID) ON DELETE CASCADE;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (teaserImageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_attachment ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_attachment ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_bbcode ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_bbcode_attribute ADD FOREIGN KEY (bbcodeID) REFERENCES wcf1_bbcode (bbcodeID) ON DELETE CASCADE;

ALTER TABLE wcf1_bbcode_media_provider ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_box ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (linkPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_box_content ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;

ALTER TABLE wcf1_category ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_cli_history ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_action ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_item ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_clipboard_page ADD FOREIGN KEY (actionID) REFERENCES wcf1_clipboard_action (actionID) ON DELETE CASCADE;
ALTER TABLE wcf1_clipboard_page ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_condition ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_contact_attachment ADD FOREIGN KEY (attachmentID) REFERENCES wcf1_attachment (attachmentID) ON DELETE CASCADE;

ALTER TABLE wcf1_core_object ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_cronjob ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_cronjob_log ADD FOREIGN KEY (cronjobID) REFERENCES wcf1_cronjob (cronjobID) ON DELETE CASCADE;

ALTER TABLE wcf1_devtools_missing_language_item ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (obsoletedByUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_event_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_language_item ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_language_item ADD FOREIGN KEY (languageCategoryID) REFERENCES wcf1_language_category (languageCategoryID) ON DELETE CASCADE;
ALTER TABLE wcf1_language_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_media ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE wcf1_media ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_media ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_media_content ADD FOREIGN KEY (mediaID) REFERENCES wcf1_media (mediaID) ON DELETE CASCADE;
ALTER TABLE wcf1_media_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (parentItemID) REFERENCES wcf1_menu_item (itemID) ON DELETE SET NULL;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_modification_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_object_type ADD FOREIGN KEY (definitionID) REFERENCES wcf1_object_type_definition (definitionID) ON DELETE CASCADE;
ALTER TABLE wcf1_object_type ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_object_type_definition ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_option_category ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_compatibility ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

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

ALTER TABLE wcf1_package_update_compatibility ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_exclusion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_fromversion ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_requirement ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_optional ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_version ADD FOREIGN KEY (packageUpdateID) REFERENCES wcf1_package_update (packageUpdateID) ON DELETE CASCADE;

ALTER TABLE wcf1_paid_subscription_user ADD FOREIGN KEY (subscriptionID) REFERENCES wcf1_paid_subscription (subscriptionID) ON DELETE CASCADE;
ALTER TABLE wcf1_paid_subscription_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (subscriptionUserID) REFERENCES wcf1_paid_subscription_user (subscriptionUserID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (subscriptionID) REFERENCES wcf1_paid_subscription (subscriptionID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (paymentMethodObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_page ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_page ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page ADD FOREIGN KEY (applicationPackageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;
ALTER TABLE wcf1_page ADD FOREIGN KEY (overrideApplicationPackageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_page_box_order ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_box_order ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;

ALTER TABLE wcf1_page_content ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_registry ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_search ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_session ADD FOREIGN KEY (spiderID) REFERENCES wcf1_spider (spiderID) ON DELETE CASCADE;
ALTER TABLE wcf1_session ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_session ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

ALTER TABLE wcf1_session_virtual ADD FOREIGN KEY (sessionID) REFERENCES wcf1_session (sessionID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE wcf1_trophy ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_collapsible_content ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_group_assignment ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

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

ALTER TABLE wcf1_user_trophy ADD FOREIGN KEY (trophyID) REFERENCES wcf1_trophy (trophyID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_trophy ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_import_mapping ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_tracked_visit ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_tracked_visit ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_tracked_visit_type ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_tracked_visit_type ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user ADD FOREIGN KEY (avatarID) REFERENCES wcf1_user_avatar (avatarID) ON DELETE SET NULL;
ALTER TABLE wcf1_user ADD FOREIGN KEY (rankID) REFERENCES wcf1_user_rank (rankID) ON DELETE SET NULL;
ALTER TABLE wcf1_user ADD FOREIGN KEY (userOnlineGroupID) REFERENCES wcf1_user_group (groupID) ON DELETE SET NULL;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_user_avatar ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_follow ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_follow ADD FOREIGN KEY (followUserID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_ignore ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_ignore ADD FOREIGN KEY (ignoreUserID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (authorID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_author ADD FOREIGN KEY (notificationID) REFERENCES wcf1_user_notification (notificationID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_author ADD FOREIGN KEY (authorID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (notificationID) REFERENCES wcf1_user_notification (notificationID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_profile_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

ALTER TABLE wcf1_user_rank ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_event ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_activity_point ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_activity_point ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_authentication_failure ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_profile_visitor ADD FOREIGN KEY (ownerID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_profile_visitor ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_object_watch ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_object_watch ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_special_trophy ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_special_trophy ADD FOREIGN KEY (trophyID) REFERENCES wcf1_trophy (trophyID) ON DELETE CASCADE;

ALTER TABLE wcf1_message_embedded_object ADD FOREIGN KEY (messageObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_message_embedded_object ADD FOREIGN KEY (embeddedObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (assignedUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_moderation_queue_to_user ADD FOREIGN KEY (queueID) REFERENCES wcf1_moderation_queue (queueID) ON DELETE CASCADE;
ALTER TABLE wcf1_moderation_queue_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_like ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_like ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_like ADD FOREIGN KEY (objectUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_like ADD FOREIGN KEY (reactionTypeID) REFERENCES wcf1_reaction_type (reactionTypeID) ON DELETE CASCADE; 

ALTER TABLE wcf1_like_object ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_like_object ADD FOREIGN KEY (objectUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

/* SQL_PARSER_OFFSET */

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

ALTER TABLE wcf1_stat_daily ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_poll ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_poll_option ADD FOREIGN KEY (pollID) REFERENCES wcf1_poll (pollID) ON DELETE CASCADE;

ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (pollID) REFERENCES wcf1_poll (pollID) ON DELETE CASCADE;
ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (optionID) REFERENCES wcf1_poll_option (optionID) ON DELETE CASCADE;
ALTER TABLE wcf1_poll_option_vote ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_notice_dismissed ADD FOREIGN KEY (noticeID) REFERENCES wcf1_notice (noticeID) ON DELETE CASCADE;
ALTER TABLE wcf1_notice_dismissed ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

/* SQL_PARSER_OFFSET */

/* default inserts */
-- default user groups
INSERT INTO wcf1_user_group (groupID, groupName, groupType) VALUES (1, 'wcf.acp.group.group1', 1); -- Everyone
INSERT INTO wcf1_user_group (groupID, groupName, groupType) VALUES (2, 'wcf.acp.group.group2', 2); -- Guests
INSERT INTO wcf1_user_group (groupID, groupName, groupType) VALUES (3, 'wcf.acp.group.group3', 3); -- Registered Users
INSERT INTO wcf1_user_group (groupID, groupName, groupType) VALUES (4, 'wcf.acp.group.group4', 9); -- Administrators
INSERT INTO wcf1_user_group (groupID, groupName, groupType) VALUES (5, 'wcf.acp.group.group5', 4); -- Moderators

-- default user group options
INSERT INTO wcf1_user_group_option (optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (1, 'admin.general.canUseAcp', 'admin.general', 'boolean', '0', 1, 1);
INSERT INTO wcf1_user_group_option (optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (2, 'admin.configuration.package.canInstallPackage', 'admin.configuration.package', 'boolean', '0', 1, 1);
INSERT INTO wcf1_user_group_option (optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (3, 'admin.user.canEditGroup', 'admin.user.group', 'boolean', '0', 1, 1);

-- default user group option values
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 1, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 2, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 3, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 1, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 2, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 3, '1');	-- Administrators

-- default update servers
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/2019/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/2019/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/vortex/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/tornado/', 'online', 0, NULL, 0, '', '');

-- style default values
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('individualScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('messageSidebarOrientation', 'left');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('overrideScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogo', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoWidth', '281');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoHeight', '40');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoMobile', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useFluidLayout', '1');
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBackground', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBorder', 'rgba(236, 241, 247, 1)');
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonBackgroundActive', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorButtonTextDisabled', 'rgba(165, 165, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorTableBorder', 'rgba(221, 221, 221, 1)');
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackground', 'rgba(43, 79, 113, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackground', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)');
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMinWidth', '1000px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationBackground', 'rgba(236, 239, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationText', 'rgba(170, 170, 170, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageThemeColor', ''); -- uses `$wcfHeaderBackground` if left empty
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
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLink', 'rgba(132, 53, 52, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLinkActive', 'rgba(132, 53, 52, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorText', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBackground', 'rgba(217, 237, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBorder', 'rgba(188, 223, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLink', 'rgba(36, 82, 105, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLinkActive', 'rgba(36, 82, 105, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoText', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBackground', 'rgba(223, 240, 216, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBorder', 'rgba(208, 233, 198, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLink', 'rgba(43, 84, 44, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLinkActive', 'rgba(43, 84, 44, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessText', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBackground', 'rgba(252, 248, 227, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBorder', 'rgba(250, 242, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLink', 'rgba(102, 81, 44, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLinkActive', 'rgba(102, 81, 44, 1)');
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
	
-- default priorities
UPDATE wcf1_user_group SET priority = 10 WHERE groupID = 3;
UPDATE wcf1_user_group SET priority = 1000 WHERE groupID = 4;
UPDATE wcf1_user_group SET priority = 100 WHERE groupID = 5;

-- default 'showOnTeamPage' setting
UPDATE wcf1_user_group SET showOnTeamPage = 1 WHERE groupID IN (4, 5);

-- default ranks
INSERT INTO wcf1_user_rank (groupID, requiredPoints, rankTitle, cssClassName) VALUES
	(4, 0, 'wcf.user.rank.administrator', 'blue'),
	(5, 0, 'wcf.user.rank.moderator', 'blue'),
	(3, 0, 'wcf.user.rank.user0', ''),
	(3, 300, 'wcf.user.rank.user1', ''),
	(3, 900, 'wcf.user.rank.user2', ''),
	(3, 3000, 'wcf.user.rank.user3', ''),
	(3, 9000, 'wcf.user.rank.user4', ''),
	(3, 15000, 'wcf.user.rank.user5', '');

-- default options: subject and message
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, required, showOrder, originIsSystem) VALUES (1, 'wcf.contact.option1', 'wcf.contact.optionDescription1', 'text', 1, 1, 1);
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, required, showOrder, originIsSystem) VALUES (2, 'wcf.contact.option2', '', 'textarea', 1, 1, 1);

-- default recipient: site administrator
INSERT INTO wcf1_contact_recipient (recipientID, name, email, isAdministrator, originIsSystem) VALUES (1, 'wcf.contact.recipient.name1', '', 1, 1);

-- default reaction type
INSERT INTO wcf1_reaction_type (reactionTypeID, title, showOrder, iconFile) VALUES (1, 'wcf.reactionType.title1', 1, 'like.svg');
INSERT INTO wcf1_reaction_type (reactionTypeID, title, showOrder, iconFile) VALUES (2, 'wcf.reactionType.title2', 2, 'thanks.svg');
INSERT INTO wcf1_reaction_type (reactionTypeID, title, showOrder, iconFile) VALUES (3, 'wcf.reactionType.title3', 3, 'haha.svg');
INSERT INTO wcf1_reaction_type (reactionTypeID, title, showOrder, iconFile) VALUES (4, 'wcf.reactionType.title4', 4, 'confused.svg');
INSERT INTO wcf1_reaction_type (reactionTypeID, title, showOrder, iconFile) VALUES (5, 'wcf.reactionType.title5', 5, 'sad.svg');
