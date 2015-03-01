/* 
 * ##################
 * ### NEW TABLES ###
 * ##################
 */

DROP TABLE IF EXISTS wcf1_ad;
CREATE TABLE wcf1_ad (
	adID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	adName VARCHAR(255) NOT NULL,
	ad MEDIUMTEXT,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0,
	showOrder INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_captcha_question;
CREATE TABLE wcf1_captcha_question (
	questionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	question VARCHAR(255) NOT NULL,
	answers MEDIUMTEXT,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_condition;
CREATE TABLE wcf1_condition (
	conditionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	conditionData MEDIUMTEXT
);

DROP TABLE IF EXISTS wcf1_edit_history_entry;
CREATE TABLE wcf1_edit_history_entry (
	entryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	userID INT(10),
	username VARCHAR(255) NOT NULL DEFAULT '',
	time INT(10) NOT NULL DEFAULT 0, -- time the version was created, displayed to the user
	obsoletedAt INT(10) NOT NULL DEFAULT 0, -- time the version was inserted into the edit history, used for clean up
	obsoletedByUserID INT(10),
	message MEDIUMTEXT,
	editReason TEXT,
	
	KEY (objectTypeID, objectID),
	KEY (obsoletedAt, obsoletedByUserID)
);

DROP TABLE IF EXISTS wcf1_message_embedded_object;
CREATE TABLE wcf1_message_embedded_object (
	messageObjectTypeID INT(10) NOT NULL,
	messageID INT(10) NOT NULL,
	embeddedObjectTypeID INT(10) NOT NULL,
	embeddedObjectID INT(10) NOT NULL,
	
	KEY (messageObjectTypeID, messageID)
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

DROP TABLE IF EXISTS wcf1_session_virtual;
CREATE TABLE wcf1_session_virtual (
	virtualSessionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID CHAR(40) NOT NULL,
	ipAddress VARCHAR(39) NOT NULL DEFAULT '',
	userAgent VARCHAR(255) NOT NULL DEFAULT '',
	lastActivityTime INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (sessionID, ipAddress, userAgent)
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

DROP TABLE IF EXISTS wcf1_user_group_assignment;
CREATE TABLE wcf1_user_group_assignment (
	assignmentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupID INT(10) NOT NULL,
	title VARCHAR(255) NOT NULL,
	isDisabled TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_notification_author;
CREATE TABLE wcf1_user_notification_author (
	notificationID INT(10) NOT NULL,
	authorID INT(10),
	time INT(10) NOT NULL DEFAULT 0,
	UNIQUE KEY (notificationID, authorID)
);

/* 
 * ############################
 * ### DROP EXISTING TABLES ###
 * ############################
 */

DROP TABLE wcf1_search_index;

/* 
 * ##############################
 * ### MODIFY EXISTING TABLES ###
 * ##############################
 */

ALTER TABLE wcf1_bbcode ADD originIsSystem TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_label_group ADD showOrder INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_label_group ADD groupDescription VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE wcf1_moderation_queue ADD comments SMALLINT(5) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_moderation_queue DROP FOREIGN KEY objectTypeID;
ALTER TABLE wcf1_moderation_queue DROP KEY affectedObject;
ALTER TABLE wcf1_moderation_queue ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

/* change default value to '1' */
ALTER TABLE wcf1_like CHANGE time time INT(10) NOT NULL DEFAULT 1;

ALTER TABLE wcf1_package_update_server ADD apiVersion ENUM('2.0', '2.1') NOT NULL DEFAULT '2.0';
ALTER TABLE wcf1_package_update_server ADD metaData TEXT;

ALTER TABLE wcf1_page_menu_item ADD originIsSystem TINYINT(1) NOT NULL DEFAULT 0;

/* truncate table to ensure consistency */
DELETE FROM wcf1_session;
DELETE FROM wcf1_session_virtual;

ALTER TABLE wcf1_session ADD UNIQUE KEY uniqueUserID (userID);

ALTER TABLE wcf1_sitemap ADD permissions TEXT NULL;
ALTER TABLE wcf1_sitemap ADD options TEXT NULL;

ALTER TABLE wcf1_template_listener ADD niceValue TINYINT(3) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_user_group_option ADD usersOnly TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_user_menu_item ADD iconClassName VARCHAR(255) NOT NULL DEFAULT '';

/* truncate table to ensure consistency */
DELETE FROM wcf1_user_notification;

ALTER TABLE wcf1_user_notification CHANGE authorID authorID INT(10) NULL;
ALTER TABLE wcf1_user_notification ADD timesTriggered INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_notification ADD guestTimesTriggered INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_notification ADD userID INT(10) NOT NULL;
ALTER TABLE wcf1_user_notification ADD mailNotified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_notification ADD confirmTime INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_notification ADD baseObjectID INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_notification ADD KEY userRelatedFields (userID, eventID, objectID, confirmTime);
ALTER TABLE wcf1_user_notification ADD KEY userConfirmTime (userID, confirmTime);
ALTER TABLE wcf1_user_notification DROP KEY eventHash;
ALTER TABLE wcf1_user_notification DROP FOREIGN KEY packageID;
ALTER TABLE wcf1_user_notification DROP KEY packageID;

ALTER TABLE wcf1_user_notification_to_user DROP mailNotified;

ALTER TABLE wcf1_user_notification_event ADD presetMailNotificationType ENUM('none', 'instant', 'daily') NOT NULL DEFAULT 'none';

ALTER TABLE wcf1_user_option ADD originIsSystem TINYINT(1) NOT NULL DEFAULT 0;

/* 
 * ####################
 * ### FOREIGN KEYS ###
 * ####################
 */

ALTER TABLE wcf1_ad ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_condition ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_edit_history_entry ADD FOREIGN KEY (obsoletedByUserID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_paid_subscription_user ADD FOREIGN KEY (subscriptionID) REFERENCES wcf1_paid_subscription (subscriptionID) ON DELETE CASCADE;
ALTER TABLE wcf1_paid_subscription_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (subscriptionUserID) REFERENCES wcf1_paid_subscription_user (subscriptionUserID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (subscriptionID) REFERENCES wcf1_paid_subscription (subscriptionID) ON DELETE SET NULL;
ALTER TABLE wcf1_paid_subscription_transaction_log ADD FOREIGN KEY (paymentMethodObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_session_virtual ADD FOREIGN KEY (sessionID) REFERENCES wcf1_session (sessionID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wcf1_user_group_assignment ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_author ADD FOREIGN KEY (notificationID) REFERENCES wcf1_user_notification (notificationID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_author ADD FOREIGN KEY (authorID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_authentication_failure ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_message_embedded_object ADD FOREIGN KEY (messageObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_message_embedded_object ADD FOREIGN KEY (embeddedObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_stat_daily ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_notice_dismissed ADD FOREIGN KEY (noticeID) REFERENCES wcf1_notice (noticeID) ON DELETE CASCADE;
ALTER TABLE wcf1_notice_dismissed ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

/* 
 * #######################
 * ### STYLE VARIABLES ###
 * #######################
 */

UPDATE wcf1_style_variable SET defaultValue = '0px' WHERE variableName = 'wcfContainerBorderRadius';
DELETE FROM wcf1_style_variable WHERE variableName = 'wcfMainMenuHoverBackgroundColor';

/* 
 * #######################
 * ### MEDIA PROVIDERS ###
 * #######################
 */

INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Soundcloud set', 'https?://soundcloud.com/(?P<artist>[a-zA-Z0-9_-]+)/sets/(?P<name>[a-zA-Z0-9_-]+)', '<iframe width="100%" height="450" scrolling="no" src="https://w.soundcloud.com/player/?url=http%3A%2F%2Fsoundcloud.com%2F{$artist}%2Fsets%2F{$name}"></iframe>');
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://soundcloud.com/(?P<artist>[a-zA-Z0-9_-]+)/(?!sets/)(?P<song>[a-zA-Z0-9_-]+)' WHERE title = 'Soundcloud';
UPDATE wcf1_bbcode_media_provider SET html = '<iframe style="max-width:100%;" width="560" height="315" src="https://www.youtube-nocookie.com/embed/{$ID}?wmode=transparent{$start}" allowfullscreen></iframe>' WHERE title = 'YouTube';
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://vimeo\\.com/(?P<ID>\\d+)', html = '<iframe src="https://player.vimeo.com/video/{$ID}" width="400" height="225" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' WHERE title = 'Vimeo';

/* 
 * ##############################
 * ### TYPHOON UPDATE SERVERS ###
 * ##############################
 */

INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/typhoon/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/typhoon/', 'online', 0, NULL, 0, '', '');

/* 
 * ########################
 * ### MINOR UPDATES ######
 * ########################
 */

/* change default value to '1' */
UPDATE wcf1_like SET time = 1 WHERE time = 0;
