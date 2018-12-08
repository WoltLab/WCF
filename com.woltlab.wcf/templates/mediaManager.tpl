{hascontent}
	<div class="mediaManagerCategoryList">
		<select name="categoryID" class="fullWidth">
			<option value="0">{lang}wcf.media.category.choose{/lang}</option>
			
			{content}
				{foreach from=$categoryList item=categoryItem}
					<option value="{$categoryItem->categoryID}">{$categoryItem->getTitle()}</option>
					
					{if $categoryItem->hasChildren()}
						{foreach from=$categoryItem item=subCategoryItem}
							<option value="{$subCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;{$subCategoryItem->getTitle()}</option>
							
							{if $subCategoryItem->hasChildren()}
								{foreach from=$subCategoryItem item=subSubCategoryItem}
									<option value="{$subSubCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$subSubCategoryItem->getTitle()}</option>
								{/foreach}
							{/if}
						{/foreach}
					{/if}
				{/foreach}
			{/content}
		</select>
	</div>
{/hascontent}

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

<div class="paginationBottom jsPagination"></div>
