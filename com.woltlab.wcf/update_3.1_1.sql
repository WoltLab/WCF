ALTER TABLE wcf1_comment DROP FOREIGN KEY objectTypeID;
ALTER TABLE wcf1_comment DROP KEY objectTypeID;
ALTER TABLE wcf1_comment ADD KEY (objectTypeID, objectID, isDisabled, time);
ALTER TABLE wcf1_comment ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
