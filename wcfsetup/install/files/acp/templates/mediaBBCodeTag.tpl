{if !$removeLinks|isset}{assign var='removeLinks' value=false}{/if}
<span class="mediaBBCode{if $float != 'none'} messageFloatObject{$float|ucfirst}{/if}">
	{if $thumbnailSize != 'original'}
		{if !$removeLinks}<a href="{$mediaLink}" class="embeddedAttachmentLink jsImageViewer">{/if}<img src="{$thumbnailLink}" alt="{$media->altText}" title="{$media->title}" data-width="{@$media->getThumbnailWidth($thumbnailSize)}" data-height="{@$media->getThumbnailHeight($thumbnailSize)}">{if !$removeLinks}</a>{/if}
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
