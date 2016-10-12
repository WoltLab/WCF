<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="10">
	{foreach from=$categoryList item=categoryItem}
		<option value="{@$categoryItem->categoryID}"{if $categoryItem->categoryID|in_array:$value} selected{/if}>{$categoryItem->getTitle()}</option>
		
		{if $categoryItem->hasChildren()}
			{foreach from=$categoryItem item=subCategoryItem}
				<option value="{@$subCategoryItem->categoryID}"{if $subCategoryItem->categoryID|in_array:$value} selected{/if}>&nbsp;&nbsp;&nbsp;&nbsp;{$subCategoryItem->getTitle()}</option>
			{/foreach}
		{/if}
	{/foreach}
</select>