-- The database API requires this column in order to work properly. DO NOT MOVE THIS! 
ALTER TABLE wcf1_package_installation_sql_log ADD COLUMN isDone TINYINT(1) NOT NULL DEFAULT 1;
