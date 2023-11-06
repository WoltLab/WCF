{hascontent}
	<div class="mediaManagerCategoryList">
		<select name="categoryID" class="fullWidth">
			<option value="0">{lang}wcf.global.categories{/lang}</option>
			<option value="-1">{lang}wcf.media.category.choose.noCategory{/lang}</option>
			
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
		<button type="button" class="button mediaManagerSearchCancelButton jsTooltip" title="{lang}wcf.media.search.cancel{/lang}">
			{icon name='xmark'}
		</button>
	</span>
</div>

{if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
	<div class="mediaManagerMediaUploadButton"></div>
{/if}

<div class="jsClipboardContainer" data-type="com.woltlab.wcf.media">
	<input type="checkbox" class="jsClipboardMarkAll" style="display: none;">
	<ul class="mediaManagerMediaList jsObjectActionContainer" data-object-action-class-name="wcf\data\media\MediaAction">
		{include file='mediaListItems'}
	</ul>
</div>

<div class="paginationBottom jsPagination"></div>
