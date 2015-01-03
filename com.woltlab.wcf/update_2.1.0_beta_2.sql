ALTER TABLE wcf1_package_update_server ADD apiVersion ENUM('2.0', '2.1') NOT NULL DEFAULT '2.0';
ALTER TABLE wcf1_package_update_server ADD metaData TEXT;