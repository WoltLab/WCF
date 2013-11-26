/* d2fbb3b */
ALTER TABLE wcf1_user_rank CHANGE groupID groupID INT(10) NOT NULL;
ALTER TABLE wcf1_user_rank ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;
