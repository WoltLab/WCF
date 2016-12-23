<span class="mediaBBCode{if $float != 'none'} messageFloatObject{$float|ucfirst}{/if}">
	{if $thumbnailSize != 'original'}
		<a href="{$media->getLink()}" class="embeddedAttachmentLink jsImageViewer"><img src="{$media->getThumbnailLink($thumbnailSize)}" alt="{$media->altText}" title="{$media->title}"></a>
	{else}
		<img src="{$media->getLink()}" alt="{$media->altText}" title="{$media->title}">
	{/if}
		
	{if $media->caption}
		<span class="mediaBBCodeCaption">{$media->caption}</span>
	{/if}
</span>
