DELETE FROM wcf1_style_variable WHERE variableName = 'useGoogleFont';

-- Purge the existing official package servers to clean up any mess.
DELETE FROM wcf1_package_update_server WHERE LOWER(serverURL) REGEXP 'https?://(store|update)\.woltlab\.com/.*';

-- Insert the default official package servers that will be dynamically adjusted.
INSERT INTO wcf1_package_update_server (serverURL) VALUES ('http://update.woltlab.com/5.3/'), ('http://store.woltlab.com/5.3/');
