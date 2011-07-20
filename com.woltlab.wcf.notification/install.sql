-- notifications
DROP TABLE IF EXISTS wcf1_user_notification;
CREATE TABLE wcf1_user_notification (
	notificationID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	eventID INT(10) NOT NULL,
	objectID INT(10) NOT NULL DEFAULT 0,
	time INT(10) NOT NULL DEFAULT 0,
	shortOutput VARCHAR(255) DEFAULT NULL,
	mediumOutput TEXT,
	longOutput TEXT,
	additionalData TEXT
);

-- notification recipients
DROP TABLE IF EXISTS wcf1_user_notification_to_user;
CREATE TABLE wcf1_user_notification_to_user (
	notificationID INT(10) NOT NULL,
	userID INT(10) NOT NULL,
	confirmed TINYINT(1) NOT NULL DEFAULT 0,
	confirmationTime INT(10) NOT NULL DEFAULT 0,
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
	languageCategory VARCHAR(255) NOT NULL,
	defaultNotificationTypeID INT(10) NULL,
	requiresConfirmation TINYINT(1) NOT NULL DEFAULT 0,
	acceptURL VARCHAR(255) NOT NULL DEFAULT '',
	declineURL VARCHAR(255) NOT NULL DEFAULT '',
	permissions TEXT,
	options TEXT,
	UNIQUE KEY packageID (packageID, eventName)
);

-- user configuration for events
DROP TABLE IF EXISTS wcf1_user_notification_event_to_user;
CREATE TABLE wcf1_user_notification_event_to_user (
	userID INT(10) NOT NULL,
	eventID INT(10) NOT NULL,
	notificationTypeID INT(10) NOT NULL,
	enabled TINYINT(1) NOT NULL DEFAULT 0
);

-- objects that create notifications
DROP TABLE IF EXISTS wcf1_user_notification_object_type;
CREATE TABLE wcf1_user_notification_object_type (
	objectTypeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	objectType VARCHAR(255) NOT NULL,
	className VARCHAR(255) NOT NULL,  
	permissions TEXT,
	options TEXT,
	UNIQUE KEY packageID (packageID, objectType)
);

-- notification types (pm, mail, ...)
DROP TABLE IF EXISTS wcf1_user_notification_type;
CREATE TABLE wcf1_user_notification_type (
	notificationTypeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	packageID INT(10) NOT NULL,
	notificationType VARCHAR(255) NOT NULL,
	className VARCHAR(255) NOT NULL,
	permissions TEXT,
	options TEXT,
	UNIQUE KEY packageID (packageID, notificationType) 
);

ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (notificationID) REFERENCES wcf1_user_notification (notificationID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_user_notification_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event ADD FOREIGN KEY (defaultNotificationTypeID) REFERENCES wcf1_user_notification_type (notificationTypeID) ON DELETE SET NULL;

ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (eventID) REFERENCES wcf1_user_notification_event (eventID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_notification_event_to_user ADD FOREIGN KEY (notificationTypeID) REFERENCES wcf1_user_notification_type (notificationTypeID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_object_type ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_user_notification_type ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;