/* 495a4c11a530a91c4d3320124752d4157ac48ec7 */
ALTER TABLE wcf1_application ADD COLUMN landingPageID INT(10) NULL;
ALTER TABLE wcf1_application ADD FOREIGN KEY (landingPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

UPDATE wcf1_application SET landingPageID = (SELECT pageID FROM wcf1_page WHERE isLandingPage = 1 LIMIT 1) WHERE packageID = 1;
