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

ALTER TABLE wcf1_package_compatibility ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_package_update_compatibility ADD FOREIGN KEY (packageUpdateVersionID) REFERENCES wcf1_package_update_version (packageUpdateVersionID) ON DELETE CASCADE;

INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/tornado/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/tornado/', 'online', 0, NULL, 0, '', '');
