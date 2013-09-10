/* c12b1bc */
ALTER TABLE wcf1_comment DROP COLUMN lastResponseIDs;
ALTER TABLE wcf1_comment ADD COLUMN responseIDs VARCHAR(255) NOT NULL DEFAULT '';