/* 9597efd */
ALTER TABLE wcf1_comment ADD KEY lastCommentTime (userID, time);
ALTER TABLE wcf1_comment ADD KEY lastResponseTime (userID, time);