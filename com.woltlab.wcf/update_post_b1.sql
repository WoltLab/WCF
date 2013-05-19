CREATE UNIQUE INDEX applicationTemplate ON wcf1_acp_template (application, templateName);

CREATE UNIQUE INDEX applicationFile ON wcf1_package_installation_file_log (application, filename);

CREATE UNIQUE INDEX applicationTemplate ON wcf1_template (application, templateGroupID templateName);
