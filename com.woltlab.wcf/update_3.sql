ALTER TABLE wcf1_session ADD pageID INT(10);
ALTER TABLE wcf1_session ADD pageObjectID INT(10);
ALTER TABLE wcf1_session ADD parentPageID INT(10);
ALTER TABLE wcf1_session ADD parentPageObjectID INT(10);
ALTER TABLE wcf1_session ADD KEY pageID (pageID, pageObjectID);
ALTER TABLE wcf1_session ADD KEY parentPageID (parentPageID, parentPageObjectID);

DROP TABLE IF EXISTS wcf1_sitemap;

ALTER TABLE wcf1_smiley ADD smileyPath2x VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE wcf1_style ADD packageName VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE wcf1_style ADD isTainted TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_template_listener ADD permissions TEXT;
ALTER TABLE wcf1_template_listener ADD options TEXT;

ALTER TABLE wcf1_user CHANGE lostPasswordKey lostPasswordKey CHAR(40) DEFAULT NULL;
