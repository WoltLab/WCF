DELETE FROM wcf1_style_variable WHERE variableName = 'useGoogleFont';

DELETE FROM wcf1_package_update_server WHERE (serverURL LIKE '%//update.woltlab.com%' OR serverURL LIKE '%//store.woltlab.com%') AND serverURL NOT LIKE '%/5.3%';
