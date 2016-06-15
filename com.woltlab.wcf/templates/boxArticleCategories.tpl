<ol class="boxMenu">
	{foreach from=$categoryList item=categoryItem}
		<li{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} class="active"{/if} data-category-id="{@$categoryItem->categoryID}">
			<a href="{@$categoryItem->getLink()}" class="boxMenuLink">{$categoryItem->getTitle()}</a>
			
			{if $activeCategory && ($activeCategory->categoryID == $categoryItem->categoryID || $activeCategory->isParentCategory($categoryItem->getDecoratedObject())) && $categoryItem->hasChildren()}
				<ol class="boxMenuDepth1">
					{foreach from=$categoryItem item=subCategoryItem}
						<li{if $activeCategory && $activeCategory->categoryID == $subCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subCategoryItem->categoryID}">
							<a href="{@$subCategoryItem->getLink()}" class="boxMenuLink">{$subCategoryItem->getTitle()}</a>
							
							{if $activeCategory && ($activeCategory->categoryID == $subCategoryItem->categoryID || $activeCategory->parentCategoryID == $subCategoryItem->categoryID) && $subCategoryItem->hasChildren()}
								<ol class="boxMenuDepth2">
									{foreach from=$subCategoryItem item=subSubCategoryItem}
										<li{if $activeCategory && $activeCategory->categoryID == $subSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubCategoryItem->categoryID}">
											<a href="{@$subSubCategoryItem->getLink()}" class="boxMenuLink">{$subSubCategoryItem->getTitle()}</a>
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
</ol>
