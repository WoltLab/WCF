ALTER TABLE wcf1_acp_template ADD UNIQUE KEY applicationTemplate (application, templateName);

ALTER TABLE wcf1_package_installation_file_log ADD UNIQUE KEY applicationFile (application, filename);

ALTER TABLE wcf1_template ADD UNIQUE KEY applicationTemplate (application, templateGroupID templateName);