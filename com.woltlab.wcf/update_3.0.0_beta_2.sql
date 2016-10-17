/* 1535ab4ef15edc761a02e91e25e28e866716c0b5 */
ALTER TABLE wcf1_application DROP COLUMN cookiePath;

/* d3925b67b9eba7eaef81887a0cf772dcfc5baa15 */
UPDATE wcf1_style_variable SET defaultValue = 'rgba(217, 220, 222, 1)' WHERE variableName = 'wcfFooterCopyrightLink';

/* 640a8bea2af6fd577932e9a083fc800c7d1a1190 */
DELETE FROM wcf1_style_variable WHERE variableName IN ('wcfButtonBorder', 'wcfButtonBorderActive', 'wcfButtonDisabledBorder', 'wcfButtonPrimaryBorder', 'wcfButtonPrimaryBorderActive', 'wcfDropdownBorder');
