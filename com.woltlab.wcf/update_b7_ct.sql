/* 8d8368e */
CREATE TABLE wcf1_import_mapping (
	importHash CHAR(8) NOT NULL,
	objectTypeID INT(10) NOT NULL,
	oldID VARCHAR(255) NOT NULL,
	newID INT(10) NOT NULL,
	UNIQUE KEY (importHash, objectTypeID, oldID)
);
ALTER TABLE wcf1_import_mapping ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;