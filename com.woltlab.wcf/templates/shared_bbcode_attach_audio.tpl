<span class="mediaBBCode">
	<audio src="{$attachment->getLink()}" style="display: none;" id="attachmentAudio_{$attachmentIdentifier}" controls></audio>
	
	<span class="mediaBBCodeCaption">
		<a href="{$attachment->getLink()}">{$attachment->filename}</a>
	</span>
</span>

<script data-relocate="true">
	(function () {
		{* try to determine if browser might be able to play audio *}
		var audio = elById('attachmentAudio_{@$attachmentIdentifier}');
		var canPlayType = audio.canPlayType('{$attachment->fileType}');
		
		if (canPlayType === '') {
			elRemove(audio);
		}
		else {
			elShow(audio);
		}
	})();
</script>
