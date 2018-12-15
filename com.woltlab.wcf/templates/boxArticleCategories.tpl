<ol class="boxMenu">
	{foreach from=$categoryList item=categoryItem}
		<li{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} class="active"{/if} data-category-id="{@$categoryItem->categoryID}">
			<a href="{@$categoryItem->getLink()}" class="boxMenuLink">
				<span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
				<span class="badge">{#$categoryItem->getArticles()}</span>
			</a>
			
			{if $categoryItem->hasChildren() && (!$categoryItem->parentCategoryID || ($activeCategory->categoryID == $categoryItem->categoryID || $activeCategory->isParentCategory($categoryItem->getDecoratedObject())))}
				<ol class="boxMenuDepth1">
					{foreach from=$categoryItem item=subCategoryItem}
						<li{if $activeCategory && $activeCategory->categoryID == $subCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subCategoryItem->categoryID}">
							<a href="{@$subCategoryItem->getLink()}" class="boxMenuLink">
								<span class="boxMenuLinkTitle">{$subCategoryItem->getTitle()}</span>
								<span class="badge">{#$subCategoryItem->getArticles()}</span>
							</a>
							
							{if $activeCategory && ($activeCategory->categoryID == $subCategoryItem->categoryID || $activeCategory->parentCategoryID == $subCategoryItem->categoryID) && $subCategoryItem->hasChildren()}
								<ol class="boxMenuDepth2">
									{foreach from=$subCategoryItem item=subSubCategoryItem}
										<li{if $activeCategory && $activeCategory->categoryID == $subSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubCategoryItem->categoryID}">
											<a href="{@$subSubCategoryItem->getLink()}" class="boxMenuLink">
												<span class="boxMenuLinkTitle">{$subSubCategoryItem->getTitle()}</span>
												<span class="badge">{#$subSubCategoryItem->getArticles()}</span>
											</a>
										</li>
									{/foreach}
								</ol>
							{/if}
						</li>
					{/foreach}
				</ol>
			{/if}
		</li>
	{/foreach}
	
	{if $activeCategory}
		<li class="boxMenuResetFilter">
			<a href="{link controller='ArticleList'}{/link}" class="boxMenuLink">
				<span class="boxMenuLinkTitle">{lang}wcf.global.button.resetFilter{/lang}</span>
			</a>
		</li>
	{/if}
</ol>
