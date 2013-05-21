ALTER TABLE wcf1_acp_template ADD COLUMN application VARCHAR(255) NOT NULL;

ALTER TABLE wcf1_package_installation_file_log ADD COLUMN application VARCHAR(255) NOT NULL;

ALTER TABLE wcf1_template DROP COLUMN obsolete;
ALTER TABLE wcf1_template ADD COLUMN application VARCHAR(255) NOT NULL;
ALTER TABLE wcf1_template ADD COLUMN lastModificationTime INT(10) NOT NULL DEFAULT 0;

ALTER TABLE wcf1_template_group CHANGE parentTemplateGroupID parentTemplateGroupID INT(10) NULL;
ALTER TABLE wcf1_template_group ADD FOREIGN KEY (parentTemplateGroupID) REFERENCES wcf1_template_group (templateGroupID) ON DELETE SET NULL;

ALTER TABLE wcf1_smiley CHANGE COLUMN smileyCategoryID categoryID INT(10) NULL;
ALTER TABLE wcf1_smiley CHANGE COLUMN aliases aliases TEXT;
ALTER TABLE wcf1_smiley CHANGE COLUMN showOrder showOrder INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_smiley ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

DELETE FROM wcf1_bbcode_media_provider;

-- media providers
-- Videos
	-- Youtube
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouTube', 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/watch\\?(?:.*?&)?v=)(?<ID>[a-zA-Z0-9_-]+)(?<start>#t=(?:\\d+|(?:\\d+h(?:\\d+m)?(?:\\d+s)?)|(?:\\d+m(?:\\d+s)?)|(?:\\d+s))$)?', '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{$ID}?wmode=transparent{$start}" frameborder="0" allowfullscreen></iframe>');
	-- Vimeo
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Vimeo', 'http://vimeo\\.com/(?<ID>\\d+)', '<iframe src="http://player.vimeo.com/video/{$ID}" width="400" height="225" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
	-- MyVideo
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('MyVideo', 'http://(?:www\\.)?myvideo\\.de/watch/(?<ID>\\d+)', '<object style="width:611px;height:383px;" width="611" height="383"><embed src="http://www.myvideo.de/movie/{$ID}" width="611" height="383" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed><param name="movie" value="http://www.myvideo.de/movie/{$ID}"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param></object>');
	-- Clipfish
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Clipfish', 'http://(?:www\\.)?clipfish\\.de/video/(?<ID>\\d+)/', '<div style="width:464px; height:404px;"><div style="width:464px; height:384px;"><iframe src="http://www.clipfish.de/embed_video/?vid={$ID}&amp;as=0&amp;col=990000" name="Clipfish Embedded Video" width="464" height="384" align="left" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe></div></div>');
	-- Veoh
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Veoh', 'http://(?:www\\.)?veoh\\.com/watch/v(?<ID>\\d+[a-zA-Z0-9]+)', '<object width="410" height="341" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1308&amp;permalinkId=v{$ID}&amp;player=videodetailsembedded&amp;videoAutoPlay=0&amp;id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1308&amp;permalinkId=v{$ID}&amp;player=videodetailsembedded&amp;videoAutoPlay=0&amp;id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="410" height="341" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>');
	-- DailyMotion
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('DailyMotion', 'https?://(?:www\\.)?dailymotion\\.com/video/(?<ID>[a-zA-Z0-9]+)', '<iframe frameborder="0" width="480" height="208" src="http://www.dailymotion.com/embed/video/{$ID}"></iframe>');
	-- YouKu
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouKu', 'https?://(?:.+?\\.)?youku\\.com/v_show/id_(?<ID>[a-zA-Z0-9_-]+)(?:\\.html)?', '<iframe height=498 width=510 src="http://player.youku.com/embed/{$ID}" frameborder="0" allowfullscreen></iframe>');
-- Misc
	-- github gist
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('github gist', 'https://gist.github.com/(?<ID>[^/]+/[0-9a-zA-Z]+)', '<script src="https://gist.github.com/{$ID}.js"> </script>');
	-- soundcloud
	INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('Soundcloud', 'https?://soundcloud.com/(?<artist>[a-zA-Z0-9_-]+)/(?<song>[a-zA-Z0-9_-]+)', '<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=http%3A%2F%2Fsoundcloud.com%2F{$artist}%2F{$song}"></iframe>');

INSERT IGNORE INTO wcf1_style_variable (variableName, defaultValue) VALUES ('messageSidebarOrientation', 'left');

ALTER TABLE wcf1_search_keyword ADD KEY (searches, lastSearchTime);

ALTER TABLE wcf1_search_index CHANGE languageID languageID INT(10) NULL;
ALTER TABLE wcf1_search_index ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_user DROP COLUMN signatureCache;

ALTER TABLE wcf1_user_avatar ADD COLUMN cropX SMALLINT(5) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_avatar ADD COLUMN cropY SMALLINT(5) NOT NULL DEFAULT 0;