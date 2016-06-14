{foreach from=$mediaList item=media}
	<li class="jsClipboardObject" data-object-id="{@$media->mediaID}">
		<div class="mediaThumbnail">
			{@$media->getElementTag(144)}
		</div>
		
		<div class="mediaInformation">
			<p class="mediaTitle">{if $media->title}{$media->title}{else}{$media->filename}{/if}</p>
		</div>
		
		<nav class="buttonGroupNavigation">
			<ul class="smallButtons buttonGroup">
				<li>
					<input type="checkbox" class="jsClipboardItem jsMediaCheckbox" data-object-id="{@$media->mediaID}">
				</li>
				{if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
					<li>
						<a><span class="icon icon16 fa-pencil jsTooltip jsMediaEditIcon" data-object-id="{@$media->mediaID}" title="{lang}wcf.global.button.edit{/lang}"></span></a>
					</li>
					<li>
						<a><span class="icon icon16 fa-times jsTooltip jsMediaDeleteIcon" data-object-id="{@$media->mediaID}" title="{lang}wcf.global.button.delete{/lang}"></span></a>
					</li>
				{/if}
				{if $mode == 'editor'}
					<li>
						<a><span class="icon icon16 fa-plus jsTooltip jsMediaInsertIcon" data-object-id="{@$media->mediaID}" title="{lang}wcf.media.button.insert{/lang}"></span></a>
					</li>
				{elseif $mode == 'select'}
					<li>
						<a><span class="icon icon16 fa-check jsTooltip jsMediaSelectIcon" data-object-id="{@$media->mediaID}" title="{lang}wcf.media.button.select{/lang}"></span></a>
					</li>
				{/if}
			</ul>
		</nav>
	</li>
{/foreach}
