{foreach from=$view->getItems() item='media'}
	<div class="mediaManager__tile listView__item"
		 role="option"
		 aria-selected="false"
		 tabindex="-1"
		 data-object-id="{$media->getObjectID()}"
		 data-filename="{$media->filename}"
		 data-details="{if $media->isImage}{lang}wcf.media.imageDimensions.value{/lang} · {/if}{$media->filesize|filesize}"
	>
		<div class="mediaManager__tile__thumbnail">
			{if $media->isImage}
				{* The image is cropped to a square, the medium thumbnail stays sharp on high resolution screens. *}
				<img src="{$media->getThumbnailLink('medium')}" alt="{$media->getAltText()}" loading="lazy">
			{else}
				{icon size=48 name=$media->getIconName()}
			{/if}

			<span class="mediaManager__tile__indicator" aria-hidden="true">{icon name='check'}</span>

			{hascontent}
				<div class="mediaManager__tile__menu">
					{content}{unsafe:$view->renderInteractionContextMenuButton($media)}{/content}
				</div>
			{/hascontent}
		</div>

		<div class="mediaManager__tile__name" title="{$media->getTitle()}">{$media->getTitle()}</div>
		<div class="mediaManager__tile__meta">
			{if $media->getTitle() !== $media->filename}{$media->filename} · {/if}{$media->filesize|filesize}
		</div>
	</div>
{/foreach}
