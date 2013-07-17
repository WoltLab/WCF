/* 83689a3 */
ALTER TABLE wcf1_user_group_option DROP COLUMN adminDefaultValue;

/* a16ee11 */
DROP TABLE wcf1_user_activity_point_event;
ALTER TABLE wcf1_user_activity_point ADD items INT(10) NOT NULL DEFAULT '0';

/* b33c97d */
ALTER TABLE wcf1_package_installation_queue DROP COLUMN confirmInstallation;
ALTER TABLE wcf1_package_installation_queue DROP COLUMN packageType;
ALTER TABLE wcf1_package_installation_queue ADD isApplication TINYINT(1) NOT NULL DEFAULT '0';