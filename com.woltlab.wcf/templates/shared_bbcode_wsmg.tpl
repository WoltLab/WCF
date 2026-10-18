<div class="mediaGallery">
	<div class="mediaGallery__track" tabindex="0" role="region" aria-label="{lang}wcf.media.gallery{/lang}">
		{foreach from=$mediaList item=media}
			<figure class="mediaGallery__slide">
				{if !$removeLinks}
					<a href="{$media->getFullSizeImageSource()}" class="mediaGallery__link" data-caption="{$media->title}" data-type="image" data-fancybox="message-{$activeMessageObjectType}-{$activeMessageID}">
				{/if}
						<img src="{$media->getThumbnailLink('large')}" alt="{$media->altText}" title="{$media->title}" width="{$media->getThumbnailWidth('large')}" height="{$media->getThumbnailHeight('large')}" loading="lazy">
				{if !$removeLinks}
					</a>
				{/if}

				{if $media->caption}
					<figcaption class="mediaGallery__caption">
						{if $media->captionEnableHtml}
							{unsafe:$media->caption}
						{else}
							{$media->caption}
						{/if}
					</figcaption>
				{/if}
			</figure>
		{/foreach}
	</div>

	<div class="mediaGallery__controls" hidden>
		<button type="button" class="mediaGallery__button mediaGallery__button--previous jsTooltip" title="{lang}wcf.media.gallery.previous{/lang}">
			{icon name='chevron-left'}
		</button>
		<span class="mediaGallery__counter" aria-live="polite"></span>
		<button type="button" class="mediaGallery__button mediaGallery__button--next jsTooltip" title="{lang}wcf.media.gallery.next{/lang}">
			{icon name='chevron-right'}
		</button>
	</div>
</div>
