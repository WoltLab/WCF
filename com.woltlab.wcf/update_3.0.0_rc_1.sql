/* c906ea240f952dcde62c84d55df42f1e878ae4e9 */
ALTER TABLE wcf1_acp_session ADD COLUMN sessionVariables MEDIUMTEXT;

DELETE FROM wcf1_session;
ALTER TABLE wcf1_session ADD COLUMN sessionVariables MEDIUMTEXT;
ALTER TABLE wcf1_session_virtual DROP COLUMN sessionVariables;
