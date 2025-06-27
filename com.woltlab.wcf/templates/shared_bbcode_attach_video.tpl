<span class="mediaBBCode">
	<video src="{$attachment->getLink()}" style="display: none;" id="attachmentVideo_{$attachmentIdentifier}" controls></video>
	
	<span class="mediaBBCodeCaption">
		<a href="{$attachment->getLink()}">{$attachment->filename}</a>
	</span>
</span>

<script data-relocate="true">
	(function () {
		{* try to determine if browser might be able to play video *}
		var video = elById('attachmentVideo_{unsafe:$attachmentIdentifier|encodeJS}');
		var canPlayType = video.canPlayType('{unsafe:$attachment->fileType|encodeJS}');
		
		if (canPlayType === '') {
			elRemove(video);
		}
		else {
			elShow(video);
		}
	})();
</script>
