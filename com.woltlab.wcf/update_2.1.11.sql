/* e80b96ef0f3490b7edccf7209424f245705f5c91 */
ALTER TABLE wcf1_user ADD KEY authData (authData);

/* 66f046bb344ff7903bb767b5982fe77758960919 */
UPDATE  wcf1_bbcode_media_provider
SET     regex = 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/(?:#/)?watch\\?(?:.*?&)?v=)(?P<ID>[a-zA-Z0-9_-]+)(?:(?:\\?|&)t=(?P<start>\\d+)$)?',
	html ='<iframe style="max-width:100%;" width="560" height="315" src="https://www.youtube.com/embed/{$ID}?wmode=transparent&amp;start={$start}" allowfullscreen></iframe>'
WHERE   title = 'YouTube';
