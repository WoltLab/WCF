<div class="inputAddon mediaManagerSearch">
	<input type="text" class="mediaManagerSearchField" placeholder="{lang}wcf.media.search.placeholder{/lang}">
	<span class="inputSuffix">
		<span class="icon icon16 fa-times mediaManagerSearchCancelButton pointer jsTooltip" title="{lang}wcf.media.search.cancel{/lang}"></span>
	</span>
</div>

{if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
	<div class="mediaManagerMediaUploadButton"></div>
{/if}

<div class="jsClipboardContainer" data-type="com.woltlab.wcf.media">
	<input type="checkbox" class="jsClipboardMarkAll" style="display: none;">
	<ul class="mediaManagerMediaList">
		{include file='mediaListItems'}
	</ul>
</div>
