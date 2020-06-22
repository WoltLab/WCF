<span id="attachmentVideo_{@$attachmentIdentifier}" class="videoContainer" style="display: none;">
	<video src="{link controller='Attachment' object=$attachment}{/link}" controls></video>
</span>

<a id="attachmentVideoLink_{@$attachmentIdentifier}" href="{link controller='Attachment' object=$attachment}{/link}">{$attachment->filename}</a>

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
