ALTER TABLE wcf1_comment_response DROP FOREIGN KEY commentID;
ALTER TABLE wcf1_comment_response DROP KEY commentID;
ALTER TABLE wcf1_comment_response ADD KEY (commentID, isDisabled, time);
ALTER TABLE wcf1_comment_response ADD FOREIGN KEY (commentID) REFERENCES wcf1_comment (commentID) ON DELETE CASCADE;
