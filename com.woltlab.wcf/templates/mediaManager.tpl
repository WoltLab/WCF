{if !$showFileTypeFilter|isset}{assign var=showFileTypeFilter value=true}{/if}
<div class="inputAddon{if $showFileTypeFilter} dropdown{/if}" id="mediaManagerSearch">
	{if $showFileTypeFilter}
		<span class="button dropdownToggle inputPrefix">
			<span class="active">{lang}wcf.media.search.filetype{/lang}</span>
		</span>
		<ul class="dropdownMenu">
			<li data-file-type="image"><span>{lang}wcf.media.search.filetype.image{/lang}</span></li>
			<li data-file-type="text"><span>{lang}wcf.media.search.filetype.text{/lang}</span></li>
			<li data-file-type="pdf"><span>{lang}wcf.media.search.filetype.pdf{/lang}</span></li>
			<li data-file-type="other"><span>{lang}wcf.media.search.filetype.other{/lang}</span></li>
			{event name='filetype'}
			<li class="dropdownDivider"></li>
			<li data-file-type="all"><span>{lang}wcf.media.search.filetype.all{/lang}</span></li>
		</ul>
	{/if}
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
