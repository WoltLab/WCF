<ol class="boxMenu">
	{foreach from=$categoryList item=categoryItem}
		{if $categoryItem->isVisibleInNestedList($activeCategory, $showChildCategories)}
			<li class="boxMenuItem boxMenuItemDepth{@$categoryItem->getDepth()}{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} active{/if}" data-category-id="{@$categoryItem->categoryID}">
				<a href="{@$categoryItem->getLink()}" class="boxMenuLink">
					<span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
					<span class="badge">{#$categoryItem->getItems()}</span>
				</a>
			</li>
		{/if}
	{/foreach}
	
	{if $activeCategory && $resetFilterLink}
		<li class="boxMenuResetFilter">
			<a href="{$resetFilterLink}" class="boxMenuLink">
				<span class="boxMenuLinkTitle">{lang}wcf.global.button.resetFilter{/lang}</span>
			</a>
		</li>
	{/if}
</ol>
