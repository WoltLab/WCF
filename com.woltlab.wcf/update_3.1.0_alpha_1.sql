ALTER TABLE wcf1_style ADD COLUMN apiVersion ENUM('3.0', '3.1') NOT NULL DEFAULT '3.0';

INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBackground', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentContainerBorder', 'rgba(236, 241, 247, 1)');
