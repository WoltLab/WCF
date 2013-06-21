/* 40381db */
ALTER TABLE wcf1_search_index DROP KEY (objectTypeID, objectID);
ALTER TABLE wcf1_search_index ADD UNIQUE KEY (objectTypeID, objectID, languageID);

/* 8ce85e3 */
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBorderRadius', '15px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSmallButtonBorderRadius', '3px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderRadius', '0');
