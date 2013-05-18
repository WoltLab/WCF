/* 6570e38 */
ALTER TABLE wcf1_template DROP COLUMN obsolete;
ALTER TABLE wcf1_template ADD COLUMN lastModificationTime INT(10) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_template_group CHANGE parentTemplateGroupID INT(10) NULL;

ALTER TABLE wcf1_template_group ADD FOREIGN KEY (parentTemplateGroupID) REFERENCES wcf1_template_group (templateGroupID) ON DELETE SET NULL;