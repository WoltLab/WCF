{foreach from=$mediaList item=media}
	<li class="jsClipboardObject mediaFile jsObjectActionObject" data-object-id="{@$media->getObjectID()}">
		<div class="mediaThumbnail">
			{@$media->getElementTag(144)}
		</div>

		{assign var='__mediaTitle' value=$media->filename}
		{if $media->title}
			{assign var='__mediaTitle' value=$media->title}
		{/if}
		<div class="mediaInformation">
			<p class="mediaTitle" title="{$__mediaTitle}">{$__mediaTitle}</p>
		</div>

		<nav class="jsMobileNavigation buttonGroupNavigation">
			<ul class="buttonList iconList">
				<li class="mediaCheckbox">
					<a><label><input type="checkbox" class="jsClipboardItem" data-object-id="{@$media->mediaID}"></label></a>
				</li>
				{if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
					<li class="jsMediaEditButton" data-object-id="{@$media->mediaID}">
						<button class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}">
							{icon size=16 name='pencil'}
						</button>
					</li>
					<li class="jsObjectAction" data-object-action="delete" data-confirm-message="{lang title=$__mediaTitle __encode=true}wcf.media.delete.confirmMessage{/lang}">
						<button class="jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
							{icon size=16 name='xmark'}
						</button>
					</li>
				{/if}
				{if $mode == 'editor'}
					<li class="jsMediaInsertButton" data-object-id="{@$media->mediaID}">
						<button class="jsTooltip" title="{lang}wcf.media.button.insert{/lang}">
							{icon size=16 name='plus'}
						</button>
					</li>
				{elseif $mode == 'select'}
					<li class="jsMediaSelectButton" data-object-id="{@$media->mediaID}">
						<button class="jsTooltip" title="{lang}wcf.media.button.select{/lang}">
							{icon size=16 name='check'}
						</button>
					</li>
				{/if}
			</ul>
		</nav>
	</li>
{/foreach}
