<div class="inputAddon" id="mediaManagerSearch">
	<input type="text" id="mediaManagerSearchField" placeholder="{lang}wcf.media.search.placeholder{/lang}">
	<span class="inputSuffix">
		<span id="mediaManagerSearchCancelButton" class="icon icon16 fa-times pointer jsTooltip" title="{lang}wcf.media.search.cancel{/lang}"></span>
	</span>
</div>

{if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
	<div id="mediaManagerMediaUploadButton"></div>
{/if}

<div class="jsClipboardContainer" data-type="com.woltlab.wcf.media">
	<input type="checkbox" class="jsClipboardMarkAll" style="display: none;">
	<ul id="mediaManagerMediaList">
		{include file='mediaListItems'}
	</ul>
</div>
