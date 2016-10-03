/* 4df658a44a7be0926c7f47510530c3774125acf6 */
UPDATE wcf1_style_variable SET defaultValue = 'rgba(36, 66, 95, 1)' WHERE variableName = 'wcfHeaderMenuDropdownBackground';
UPDATE wcf1_style_variable SET defaultValue = 'rgba(65, 121, 173, 1)' WHERE variableName = 'wcfHeaderMenuDropdownBackgroundActive';

/* ba9a2bda5ae4a62836fbeda78ad342c1090475c9 */
DELETE FROM wcf1_package_update_server;
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://update.woltlab.com/vortex/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('http://store.woltlab.com/vortex/', 'online', 0, NULL, 0, '', '');
