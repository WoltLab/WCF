/* da06d70 */
ALTER TABLE wcf1_category CHANGE parentCategoryID parentCategoryID INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_category CHANGE showOrder showOrder INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_category CHANGE time time INT(10) NOT NULL DEFAULT 0;

/* a78137a & f9fa1d1 */
DELETE FROM wcf1_style_variable WHERE variableName = 'wcfLayoutFluidGap';
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMinWidth', '980px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMaxWidth', '90%');

/* c72b4ce */
ALTER TABLE wcf1_search_index CHANGE languageID languageID INT(10) NOT NULL DEFAULT 0;