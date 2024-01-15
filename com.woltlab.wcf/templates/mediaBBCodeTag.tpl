{if !$removeLinks|isset}{assign var='removeLinks' value=false}{/if}
{if $float === 'center'}<p class="text-center">{/if}
<span class="mediaBBCode{if $float != 'none'} messageFloatObject{$float|ucfirst}{/if}"{if $width !== 'auto'} style="width: {$width}px; display: inline-flex"{/if}>
	{if $media->isImage}
		{if $thumbnailSize != 'original'}
			{if !$removeLinks}
				<a href="{$mediaLink}" class="embeddedAttachmentLink jsImageViewer">
			{/if}
					<img src="{$thumbnailLink}" alt="{$media->altText}" title="{$media->title}" width="{@$media->getThumbnailWidth($thumbnailSize)}" height="{@$media->getThumbnailHeight($thumbnailSize)}" loading="lazy">
			{if !$removeLinks}
					<span class="embeddedAttachmentLinkEnlarge">
						{icon size=24 name='magnifying-glass'}
					</span>
				</a>
			{/if}
		{else}
			<img src="{$mediaLink}" alt="{$media->altText}" title="{$media->title}" width="{@$media->width}" height="{@$media->height}" loading="lazy">
		{/if}
	{elseif $media->isVideo()}
		<video src="{$mediaLink}" controls></video>
	{elseif $media->isAudio()}
		<audio src="{$mediaLink}" controls></audio>
	{/if}

	{if $media->caption}
		<span class="mediaBBCodeCaption">
			<span class="mediaBBCodeCaptionAlignment">
				{if $media->captionEnableHtml}
					{@$media->caption}
				{else}
					{$media->caption}
				{/if}
			</span>
		</span>
	{/if}
</span>
{if $float === 'center'}</p>{/if}
