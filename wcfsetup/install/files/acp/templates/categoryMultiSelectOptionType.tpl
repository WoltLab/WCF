<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="10">
	{foreach from=$categoryList item='category'}
		{if !$maximumNestingLevel|isset || $maximumNestingLevel == -1 || $categoryNodeList->getDepth() < $maximumNestingLevel}
			<option value="{$category->categoryID}"{if $category->categoryID|in_array:$value} selected{/if}>{section name=i loop=$categoryList->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->getTitle()}</option>
		{/if}
	{/foreach}
</select>