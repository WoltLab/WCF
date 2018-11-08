<ol class="boxMenu">
	{foreach from=$categories item=categoryItem}
		<li{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} class="active"{/if} data-category-id="{@$categoryItem->categoryID}">
			<a href="{@$categoryItem->getLink()}" class="boxMenuLink">
				<span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
			</a>
		</li>
	{/foreach}
</ol>