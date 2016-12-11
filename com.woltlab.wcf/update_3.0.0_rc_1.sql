/* c906ea240f952dcde62c84d55df42f1e878ae4e9 */
ALTER TABLE wcf1_acp_session ADD COLUMN sessionVariables MEDIUMTEXT;

DELETE FROM wcf1_session;
ALTER TABLE wcf1_session ADD COLUMN sessionVariables MEDIUMTEXT;
ALTER TABLE wcf1_session_virtual DROP COLUMN sessionVariables;

/* 6123473ddc43b116ed8d6dec42501d8af5fffdfe */
ALTER TABLE wcf1_moderation_queue ADD KEY objectTypeAndID (objectTypeID, objectID);

/* 5935e8588238bef0987e62765bd364f59362d21e */
ALTER TABLE wcf1_modification_log ADD KEY objectTypeAndID (objectTypeID, objectID);
