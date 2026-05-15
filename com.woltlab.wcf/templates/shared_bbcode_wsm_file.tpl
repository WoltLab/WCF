<a href="{$media->getLink()}" class="messageAttachment jsTooltip" title="{lang}wcf.media.file.title{/lang}">
	<span class="messageAttachmentIcon">
		<span class="messageAttachmentIconDefault">
			{icon size=32 name=$media->getIconName()}
		</span>
		<span class="messageAttachmentIconDownload">
			{icon size=32 name='download'}
		</span>
	</span>
	<span class="messageAttachmentFilename">{$media->getTitle()}</span>
	<span class="messageAttachmentMeta">{lang}wcf.media.file.info{/lang}</span>
</a>
