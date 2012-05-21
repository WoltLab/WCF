{foreach from=$categoryNodeList item=category}
	<option value="{$category->objectTypeCategoryID}"{if $categoryID|isset && $categoryID == $category->objectTypeCategoryID} selected="selected"{/if}>{section name=i loop=$categoryNodeList->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->getTitle()}</option>
{/foreach}