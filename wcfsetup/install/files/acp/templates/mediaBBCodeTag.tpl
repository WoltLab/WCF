<span class="mediaBBCode{if $float != 'none'} messageFloatObject{$float|ucfirst}{/if}">
	{if $thumbnailSize != 'original'}
		<a href="{$mediaLink}" class="embeddedAttachmentLink jsImageViewer"><img src="{$thumbnailLink}" alt="{$media->altText}" title="{$media->title}" data-width="{@$media->getThumbnailWidth($thumbnailSize)}" data-height="{@$media->getThumbnailHeight($thumbnailSize)}"></a>
	{else}
		<img src="{$mediaLink}" alt="{$media->altText}" title="{$media->title}" data-width="{@$media->width}" data-height="{@$media->height}">
	{/if}
	
	{if $media->caption}
		<span class="mediaBBCodeCaption">
			{if $media->captionEnableHtml}
				{@$media->caption}
			{else}
				{$media->caption}
			{/if}
		</span>
	{/if}
</span>
