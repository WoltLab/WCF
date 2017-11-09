ALTER TABLE wcf1_comment_response ADD COLUMN enableHtml TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_style ADD COLUMN image2x VARCHAR(255) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS wcf1_registry;
CREATE TABLE wcf1_registry (
	packageID INT(10) NOT NULL,
	field VARCHAR(191) NOT NULL,
	fieldValue MEDIUMTEXT,
	
	UNIQUE KEY uniqueField (packageID, field)
);

ALTER TABLE wcf1_registry ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
