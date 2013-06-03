ALTER TABLE wcf1_acp_session CHANGE requestMethod requestMethod VARCHAR(7) NOT NULL DEFAULT '';
ALTER TABLE wcf1_acp_session_access_log CHANGE requestMethod requestMethod VARCHAR(7) NOT NULL DEFAULT '';
ALTER TABLE wcf1_session CHANGE requestMethod requestMethod VARCHAR(7) NOT NULL DEFAULT '';

UPDATE wcf1_bbcode_media_provider SET regex = 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/watch\\?(?:.*?&)?v=)(?P<ID>[a-zA-Z0-9_-]+)(?P<start>(?:#a?t=(?:\\d+|(?:\\d+h(?:\\d+m)?(?:\\d+s)?)|(?:\\d+m(?:\\d+s)?)|(?:\\d+s))$)?)' WHERE title = 'YouTube';
UPDATE wcf1_bbcode_media_provider SET regex = 'http://vimeo\\.com/(?P<ID>\\d+)' WHERE title = 'Vimeo';
UPDATE wcf1_bbcode_media_provider SET regex = 'http://(?:www\\.)?myvideo\\.de/watch/(?P<ID>\\d+)' WHERE title = 'MyVideo';
UPDATE wcf1_bbcode_media_provider SET regex = 'http://(?:www\\.)?clipfish\\.de/(?:.*?/)?video/(?P<ID>\\d+)/' WHERE title = 'Clipfish';
UPDATE wcf1_bbcode_media_provider SET regex = 'http://(?:www\\.)?veoh\\.com/watch/v(?P<ID>\\d+[a-zA-Z0-9]+)' WHERE title = 'Veoh';
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://(?:www\\.)?dailymotion\\.com/video/(?P<ID>[a-zA-Z0-9]+)' WHERE title = 'DailyMotion';
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://(?:.+?\\.)?youku\\.com/v_show/id_(?P<ID>[a-zA-Z0-9_-]+)(?:\\.html)?' WHERE title = 'YouKu';
UPDATE wcf1_bbcode_media_provider SET regex = 'https://gist.github.com/(?P<ID>[^/]+/[0-9a-zA-Z]+)' WHERE title = 'github gist';
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://soundcloud.com/(?P<artist>[a-zA-Z0-9_-]+)/(?P<song>[a-zA-Z0-9_-]+)' WHERE title = 'Soundcloud';