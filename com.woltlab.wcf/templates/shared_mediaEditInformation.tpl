<div class="box64">
	{unsafe:$media->getElementTag(64)}

	<dl class="plain dataList">
		<dt>{lang}wcf.media.filename{/lang}</dt>
		<dd>{$media->filename}</dd>

		<dt>{lang}wcf.media.filesize{/lang}</dt>
		<dd>{$media->filesize|filesize}</dd>

		{if $media->isImage}
			<dt>{lang}wcf.media.imageDimensions{/lang}</dt>
			<dd>{lang}wcf.media.imageDimensions.value{/lang}</dd>
		{/if}

		<dt>{lang}wcf.media.uploader{/lang}</dt>
		<dd>{user object=$media->getUserProfile()}</dd>

		<dt>{lang}wcf.media.uploadTime{/lang}</dt>
		<dd>{time time=$media->uploadTime}</dd>

		{if $media->downloads}
			<dt>{lang}wcf.media.downloads{/lang}</dt>
			<dd>{#$media->downloads}</dd>

			<dt>{lang}wcf.media.lastDownloadTime{/lang}</dt>
			<dd>{time time=$media->lastDownloadTime}</dd>
		{/if}
	</dl>
</div>
