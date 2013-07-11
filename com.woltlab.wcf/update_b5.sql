/* 83689a3 */
ALTER TABLE wcf1_user_group_option DROP COLUMN adminDefaultValue;

/* a16ee11 */
DROP TABLE wcf1_user_activity_point_event;
ALTER TABLE wcf1_user_activity_point ADD items INT(10) NOT NULL DEFAULT '0';