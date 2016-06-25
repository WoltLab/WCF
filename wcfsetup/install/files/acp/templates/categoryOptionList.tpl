{foreach from=$categoryNodeList item='category'}
	{if !$maximumNestingLevel|isset || $maximumNestingLevel == -1 || $categoryNodeList->getDepth() < $maximumNestingLevel}
		<option value="{$category->categoryID}"{if $categoryID|isset && $categoryID == $category->categoryID} selected{/if}>{section name=i loop=$categoryNodeList->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->getTitle()}</option>
	{/if}
{/foreach}
