DROP TABLE IF EXISTS wcf1_package_compatibility;
CREATE TABLE wcf1_package_compatibility (
	packageID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageID, version)
);

DROP TABLE IF EXISTS wcf1_package_update_compatibility;
CREATE TABLE wcf1_package_update_compatibility (
	packageUpdateVersionID INT(10) NOT NULL,
	version SMALLINT(4) NOT NULL,
	UNIQUE KEY compatibleVersion (packageUpdateVersionID, version)
);

ROP TABLE IF EXISTS wcf1_registry;
CREATE TABLE wcf1_registry (
	packageID INT(10) NOT NULL,
	field VARCHAR(191) NOT NULL,
	fieldValue MEDIUMTEXT,

	UNIQUE KEY uniqueField (packageID, field)
);

ALTER TABLE wcf1_package_compatibility ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_package_update_compatibility ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;
ALTER TABLE wcf1_registry ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;