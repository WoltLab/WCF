{foreach from=$categoryNodeList item=category}
	{if !$maximumNestingLevel|isset || $maximumNestingLevel == -2 || $categoryNodeList->getDepth() <= $maximumNestingLevel}
		<option value="{$category->objectTypeCategoryID}"{if $categoryID|isset && $categoryID == $category->objectTypeCategoryID} selected="selected"{/if}>{section name=i loop=$categoryNodeList->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->getTitle()}</option>
	{/if}
{/foreach}