<span id="attachmentVideo_{@$attachmentIdentifier}" class="videoContainer" style="display: none;">
	<video src="{$attachment->getLink()}" controls></video>
</span>

<a id="attachmentVideoLink_{@$attachmentIdentifier}" href="{$attachment->getLink()}">{$attachment->filename}</a>

<script data-relocate="true">
	{* try to determine if browser might be able to play video *}
	var video = elById('attachmentVideo_{@$attachmentIdentifier}');
	var canPlayType = elCreate('video').canPlayType('{$attachment->fileType}');
	
	if (canPlayType === '') {
		elRemove(video);
	}
	else {
		elShow(video);
		elRemove(elById('attachmentVideoLink_{@$attachmentIdentifier}'));
	}
</script>
