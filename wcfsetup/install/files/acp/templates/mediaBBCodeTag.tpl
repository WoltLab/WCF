<div class="mediaBBCode{if $float != 'none'} messageFloatObject{$float|ucfirst}{/if}">
	{if $thumbnailSize != 'original'}
		<a href="{$media->getLink()}" class="embeddedAttachmentLink jsImageViewer"><img src="{$media->getThumbnailLink($thumbnailSize)}" alt="{$media->altText}" title="{$media->title}" data-width="{@$media->getThumbnailWidth($thumbnailSize)}" data-height="{@$media->getThumbnailHeight($thumbnailSize)}"></a>
	{else}
		<img src="{$media->getLink()}" alt="{$media->altText}" title="{$media->title}" data-width="{@$media->width}" data-height="{@$media->height}">
	{/if}
	
	{if $media->caption}
		<div class="mediaBBCodeCaption">
			{if $media->captionEnableHtml}
				{@$media->caption}
			{else}
				{$media->caption}
			{/if}
		</div>
	{/if}
</div>
