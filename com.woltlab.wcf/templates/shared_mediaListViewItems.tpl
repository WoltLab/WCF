{foreach from=$view->getItems() item='media'}
	<div class="mediaList__item listView__item" data-object-id="{$media->getObjectID()}">
		<div class="mediaList__item__preview">
			{unsafe:$media->getElementTag(128)}
		</div>

		<div class="mediaList__item__content">
			<h3 class="mediaList__item__title">
				<a href="{$media->getLink()}" class="mediaList__item__link">{$media->getTitle()}</a>
			</h3>
			<div class="mediaList__item__meta">
				{if $media->getTitle() !== $media->filename}<span class="separatorRight">{$media->filename}</span> {/if}{$media->filesize|filesize}
			</div>
			<div class="mediaList__item__meta">
				<span class="separatorRight">{user object=$media->getUserProfile()}</span> {time time=$media->uploadTime}
			</div>

			{event name='content'}
		</div>

		{hascontent}
			<div class="mediaList__item__buttons listView__item__buttons">
				{content}
					{if $view->hasBulkInteractions()}
						<label class="listView__selectItem__label jsTooltip" title="{lang}wcf.clipboard.item.mark{/lang}">
							<input type="checkbox" class="listView__selectItem" aria-label="{lang}wcf.clipboard.item.mark{/lang}">
						</label>
					{/if}

					{unsafe:$view->renderInteractionContextMenuButton($media)}
				{/content}
			</div>
		{/hascontent}
	</div>
{/foreach}
