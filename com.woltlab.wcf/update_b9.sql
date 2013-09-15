/* c12b1bc */
ALTER TABLE wcf1_comment DROP COLUMN lastResponseIDs;
ALTER TABLE wcf1_comment ADD COLUMN responseIDs VARCHAR(255) NOT NULL DEFAULT '';

/* 28283ef */
ALTER TABLE wcf1_style_variable SET defaultValue = '1.7rem' WHERE variableName = 'wcfHeadlineFontSize';
ALTER TABLE wcf1_style_variable SET defaultValue = '1.4rem' WHERE variableName = 'wcfSubHeadlineFontSize';
ALTER TABLE wcf1_style_variable SET defaultValue = '1.2rem' WHERE variableName = 'wcfTitleFontSize';
ALTER TABLE wcf1_style_variable SET defaultValue = '.85rem' WHERE variableName = 'wcfSmallFontSize';