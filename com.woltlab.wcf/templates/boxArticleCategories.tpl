<ol class="boxMenu">
	{foreach from=$categoryList item=categoryItem}
		{if $categoryItem->isVisibleInNestedList($activeCategory)}
			<li class="boxMenuItem boxMenuItemDepth{@$categoryItem->getDepth()}{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} active{/if}" data-category-id="{@$categoryItem->categoryID}">
				<a href="{@$categoryItem->getLink()}" class="boxMenuLink">
					<span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
					<span class="badge">{#$categoryItem->getArticles()}</span>
				</a>
			</li>
		{/if}
	{/foreach}
	
	{if $activeCategory}
		<li class="boxMenuResetFilter">
			<a href="{link controller='ArticleList'}{/link}" class="boxMenuLink">
				<span class="boxMenuLinkTitle">{lang}wcf.global.button.resetFilter{/lang}</span>
			</a>
		</li>
	{/if}
</ol>
