-- The database API requires this column in order to work properly. DO NOT MOVE THIS! 
ALTER TABLE wcf1_package_installation_sql_log ADD COLUMN isDone TINYINT(1) NOT NULL DEFAULT 1;

-- Let's do this here too, so we won't need another step for inserting two values
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfEditorTableBorder', 'rgba(221, 221, 221, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfPageThemeColor', ''); -- uses `$wcfHeaderBackground` if left empty
