/* 40381db */
ALTER TABLE wcf1_search_index DROP INDEX objectTypeID;
ALTER TABLE wcf1_search_index ADD UNIQUE KEY objectTypeID (objectTypeID, objectID, languageID);

/* 8ce85e3 */
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBorderRadius', '15px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSmallButtonBorderRadius', '3px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderRadius', '0');

/* 133a9cf */
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowLightColor', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowDarkColor', 'rgba(0, 0, 0, .8)');
