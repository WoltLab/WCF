ALTER TABLE wcf1_like_object DROP COLUMN neutralReactions;

ALTER TABLE wcf1_reaction_type DROP COLUMN type;

ALTER TABLE wcf1_user DROP KEY positiveReactionsReceived;
ALTER TABLE wcf1_user DROP KEY negativeReactionsReceived;
ALTER TABLE wcf1_user DROP KEY neutralReactionsReceived;

ALTER TABLE wcf1_user DROP COLUMN positiveReactionsReceived;
ALTER TABLE wcf1_user DROP COLUMN negativeReactionsReceived;
ALTER TABLE wcf1_user DROP COLUMN neutralReactionsReceived;
